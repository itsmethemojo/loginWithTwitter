<?php

namespace Itsmethemojo\Authentification;

use Itsmethemojo\File\Config;

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
            throw new AuthentificationException("this twitter account is not allowed on this api");
        }
        $this->addToken($userData['id'], $userData['name']);

        if ($this->isLoggedIn()) {
            return true;
        } else {
            throw new AuthentificationException("this did not work. that's odd. :(");
        }
    }

    public function isLoggedIn()
    {
        if (!$this->hasCookieToken()) {
            return false;
        }
        $tokenData = $this->getTokenData();

        return
            isset($tokenData) &&
            $tokenData['expires'] > time();
    }

    public function getTokenUserData()
    {
        if (!$this->isLoggedIn()) {
            return [];
        }
        $tokenData = $this->getTokenData();
        return [
            "id" => $tokenData['id'],
            "handle" => $tokenData['handle']
        ];
    }

    private function hasCookieToken()
    {
        return isset($_COOKIE[self::TOKEN_KEY]);
    }

    private function getTokenData()
    {
        if (!$this->hasCookieToken()) {
            return [];
        }
        return $this->getTokenDataList()[$_COOKIE[self::TOKEN_KEY]];
    }

    private function getTokenDataList()
    {
        //TODO save tokens ones read
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

    private function addToken($id, $handle)
    {
        //TODO use redis to let the token expire by their own
        $tokenDataList = $this->getTokenDataList();
        $token = $this->createToken($userData['id']);
        $tokenDataList[$token] = array(
            'id' => $id,
            'handle' => $handle,
            'token' => $token,
            'expires' => time() + $this->tokenLifetime
        );
        file_put_contents('/var/www/data/tokens.json', json_encode($tokenDataList));
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
}
