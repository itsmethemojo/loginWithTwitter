<?php

namespace Itsmethemojo\Authentification;

use Redis;

class TwitterExtended
{
    const TOKEN_KEY = "twitter_token";

    /**
     * @var int login cookie lifetime
    **/
    private $tokenLifetime = 0;

    /**
     * @var Redis redis connection
    **/
    private $redis = null;

    /**
     * @var String prefix for redis keys
    **/
    private $prefix = null;

    /**
     * @var Array config values
    **/
    private $config = [];

    /**
     * @var Boolean dummy mode on / off
    **/
    private $dummyMode = false;

    public function __construct($config = [])
    {
        $this->config = $config;
        if (empty($this->config['LIFETIME'])) {
            throw new ConfigException("LIFETIME is missing in config");
        }
        $this->dummyMode = filter_var($this->config['DUMMY_MODE'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->tokenLifetime = intval($this->config['LIFETIME']);
    }

    public function doLogin()
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $twitter = new Twitter($this->config);
        $userData = $twitter->getLoginUser();

        // if user is not whitelisted this array is empty
        if (!key_exists('id', $userData)) {
            throw new AuthentificationException(
                "this twitter account is not in the list of allowed accounts"
            );
        }
        $this->addToken($userData['id'], $userData['name']);

        if ($this->isLoggedIn()) {
            return true;
        } else {
            throw new AuthentificationException(
                "final check after token cookie creation failed. "
                . "if this happens there is something wrong with the application configuration"
            );
        }
    }

    public function isLoggedIn()
    {
        if ($this->dummyMode) {
            return true;
        }
        return $this->hasCookieToken()
               && $this->tokenDataExists();
    }

    public function getTokenUserData()
    {
        if ($this->dummyMode) {
            return [
                "id" => '11111111',
                "handle" => 'dummyModeMan'
            ];
        }
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

    private function tokenDataExists()
    {
         return $this->getRedis()->exists($this->prefix . $_COOKIE[self::TOKEN_KEY]);
    }

    private function getTokenData()
    {
        if (!$this->hasCookieToken()) {
            return [];
        }
        $tokenData = $this->getRedis()->get($this->prefix . $_COOKIE[self::TOKEN_KEY]);
        if (!$tokenData) {
            return [];
        }
        return json_decode($tokenData, true);
    }

    private function addToken($id, $handle)
    {
        $token = $this->createToken($id);
        $this->getRedis()->setEx(
            $this->prefix . $token,
            $this->tokenLifetime,
            json_encode(
                array(
                    'id' => $id,
                    'handle' => $handle
                )
            )
        );
        $this->setTokenCookie($token);
    }

    private function createToken($userId)
    {
        return md5(time() . $userId . rand(1000, 9999));
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

    private function getRedis()
    {
        if ($this->redis !== null) {
            return $this->redis;
        }
        foreach (['REDIS_HOST','REDIS_PREFIX'] as $configKey) {
            if (empty($this->config[$configKey])) {
                throw new ConfigException($configKey . " is missing in config");
            }
        }
        $port = $this->config['REDIS_PORT'] ?? 6379;
        $this->prefix =  $this->config['REDIS_PREFIX'];
        $this->redis = new Redis();
        $this->redis->connect($this->config['REDIS_HOST'], $port);
        return $this->redis;
    }
}
