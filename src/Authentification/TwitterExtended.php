<?php

namespace Itsmethemojo\Authentification;

use Itsmethemojo\File\Config;
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
     * @var String name of ini file in config folder
    **/
    private $iniFile = null;

    /**
     * @var Boolean dummy mode on / off
    **/
    private $dummyMode = false;

    public function __construct($iniFile = 'login')
    {
        $this->iniFile = $iniFile;
        $config = Config::get($this->iniFile, array('LIFETIME'));
        if (isset($config['DUMMY_MODE'])) {
            $this->dummyMode = boolval($config['DUMMY_MODE']);
        }
        $this->tokenLifetime = intval($config['LIFETIME']);
    }

    public function doLogin()
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $twitter = new Twitter($this->iniFile);
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
        $config = Config::get($this->iniFile, array('REDIS_HOST','REDIS_PREFIX'));
        $port = $config['REDIS_PORT'] ?? 6379;
        $this->prefix =  $config['REDIS_PREFIX'];
        $this->redis = new Redis();
        $this->redis->connect($config['REDIS_HOST'], $port);
        return $this->redis;
    }
}
