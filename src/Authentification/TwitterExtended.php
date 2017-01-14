<?php

namespace Itsmethemojo\Authentification;

use Itsmethemojo\File\Config;
use Exception;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

class TwitterExtended
{
    const TOKEN_KEY = "twitter_token";

    /** @var int login cookie lifetime **/
    private $tokenLifetime = null;

    /** @var \MongoDB\Driver\Manager database **/
    private $database;
    
    private $tokens = null;

    public function __construct()
    {
        $config = Config::get('twitter', array('lifetime'));
        $this->tokenLifetime = intval($config['lifetime']);
    }

    public function getLoginUser()
    {
        if ($this->isLoggedIn()) {
            return array("id" => $this->getUserId());
        }
        $twitter = new Twitter();
        $userData = $twitter->getLoginUser();

        // if user is not whitelisted this array is empty
        if (!key_exists('id', $userData)) {
            throw new Exception("this twitter account is not allowed on this api");
        }

        $token = $this->createToken($userData['id']);
        $this->addTokenToDatabase($token, $userData['id']);
        $this->setToken($token);

        if ($this->isLoggedIn()) {
            return array("id" => $this->getUserId());
        } else {
            throw new Exception("this did not work. that's odd. :(");
        }
    }

    public function isLoggedIn()
    {
        if (!isset($_COOKIE[self::TOKEN_KEY])) {
            return false;
        }
        $activeToken = $_COOKIE[self::TOKEN_KEY];
        $tokens = $this->getTokens();
        return
            isset($tokens[$activeToken])
            && $tokens[$activeToken]->expires > time();
    }

    private function getTokens()
    {
        if ($this->tokens !== null) {
            return $this->tokens;
        }

        $documents = $this->getDatabase()->executeQuery(
            'loginWithTwitter.tokens',
            new Query(array())
        );

        $tokens = [];
        foreach ($documents as $document) {
            $tokens[$document->token] = $document;
        }
        $this->tokens = $tokens;
        return $this->tokens;
    }

    private function createToken($userId)
    {
        return md5(time() . $userId . rand(1000, 9999));
    }

    private function addTokenToDatabase($token, $userId)
    {
        $bulk = new BulkWrite();
        $bulk->insert(
            array(
                'userid' => $userId,
                'token' => $token,
                'expires' => time() + $this->tokenLifetime
            )
        );
        $this->getDatabase()->executeBulkWrite('loginWithTwitter.tokens', $bulk);

        $this->tokens = null;
    }

    private function setToken($token)
    {
        setcookie(
            self::TOKEN_KEY,
            $token,
            time() + $this->tokenLifetime + 500,
            "/",
            $_SERVER['HTTP_HOST']
        );
        $_COOKIE[self::TOKEN_KEY] = $token;
    }

    private function getUserId()
    {
        return $this->getTokens()[$_COOKIE[self::TOKEN_KEY]]->userid;
    }

    private function getDatabase()
    {
        if ($this->database === null) {
            //TODO read mongo path from ini file
            $this->database = new Manager("mongodb://localhost:27017");
            if ($this->database === null) {
                throw new Exception("mongo db connection failed");
            }
        }

        return $this->database;
    }
}
