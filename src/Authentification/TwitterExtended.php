<?php

namespace Itsmethemojo\Authentification;

use Itsmethemojo\Authentification\Twitter;
use Itsmethemojo\Storage\Database;
use Itsmethemojo\Storage\QueryParameters;
use Itsmethemojo\File\ConfigReader;
use Exception;

class TwitterExtended
{
    const TOKEN_KEY = "twitter_token";

    /** @var int login cookie lifetime **/
    private $tokenLifetime = null;

    private $tokens = null;

    public function __construct()
    {
        $this->database = new Database('login-mysql');
        $config = ConfigReader::get('twitter', array('lifetime'));
        $this->tokenLifetime = intval($config['lifetime']);
    }

    public function getLoginUser()
    {
        if ($this->isLoggedIn()) {
            return array("id" => $this->getUserId());
        }
        $twitter = new Twitter();
        $userData = $twitter->getLoginUser();
        $token = $this->createToken($userData['id']);
        $this->addTokenToDatabase($token, $userData['id']);
        $this->setToken($token);
        if ($this->isLoggedIn()) {
            return array("id" => $this->getUserId());
        } else {
            throw new Exception("login did not work?");
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
            && $tokens[$activeToken]['expires'] > time();
    }

    private function getTokens()
    {
        if ($this->tokens !== null) {
            return $this->tokens;
        }
        $dbTokens = $this->database->read(
            array('tokens'),
            "SELECT * FROM tokens"
        );
        $tokens   = array();
        foreach ($dbTokens as $dbToken) {
            $tokens[$dbToken["token"]] = $dbToken;
        }
        $this->tokens = $tokens;
        return $tokens;
    }

    private function createToken($userId)
    {
        return md5(time() . $userId . rand(1000, 9999));
    }

    private function addTokenToDatabase($token, $userId)
    {
        $params = new QueryParameters();
        $params
            ->add($token)
            ->add($userId)
            ->add(time() + $this->tokenLifetime);
        $dbTokens = $this->database->modify(
            array('tokens'),
            "INSERT INTO tokens (token,user_id,expires) VALUES (?,?,?)",
            $params
        );

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
        return $this->getTokens()[$_COOKIE[self::TOKEN_KEY]]['user_id'];
    }
}
