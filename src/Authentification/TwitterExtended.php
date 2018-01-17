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

    /**
 * @var int login cookie lifetime
**/
    private $tokenLifetime = null;

    public function __construct()
    {
        $config = Config::get('twitter', array('lifetime'));
        $this->tokenLifetime = intval($config['lifetime']);
    }

    public function doLogin()
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $twitter = new Twitter();
        $userData = $twitter->getLoginUser();

        // if user is not whitelisted this array is empty
        if (!key_exists('id', $userData)) {
            throw new Exception("this twitter account is not allowed on this api");
        }

        $token = $this->createToken($userData['id']);
        $this->addToken($token, $userData['id']);

        if ($this->isLoggedIn()) {
            return true;
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
            && $tokens[$activeToken]['expires'] > time();
    }

    private function getTokens()
    {
        //TODO use redis
        if (!file_exists('/var/www/data')) {
            mkdir('data', 0777, true);
        }

        if (!file_exists('/var/www/data/tokens.json')) {
            return [];
        }



        return json_decode(file_get_contents('/var/www/data/tokens.json'), true);
    }

    private function createToken($userId)
    {
        return md5(time() . $userId . rand(1000, 9999));
    }

    private function addToken($token, $userId)
    {
        //TODO use redis to let the token expire by their own
        $tokens = $this->getTokens();
        $tokens[$token] = array(
            'userid' => $userId,
            'token' => $token,
            'expires' => time() + $this->tokenLifetime
        );
        file_put_contents('/var/www/data/tokens.json', json_encode($tokens));
        $this->setTokenCookie($token);
    }

    private function setTokenCookie($token)
    {
        setcookie(
            self::TOKEN_KEY,
            $token,
            time() + $this->tokenLifetime + 500,
            "/"
        );
        $_COOKIE[self::TOKEN_KEY] = $token;
    }

    private function getUserId()
    {
        return $this->getTokens()[$_COOKIE[self::TOKEN_KEY]]->userid;
    }
}
