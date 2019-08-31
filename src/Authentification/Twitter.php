<?php

namespace Itsmethemojo\Authentification;

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{
    /**
     * @var String consumerKey from twitter app
    **/
    private $consumerKey    = null;

    /**
     * @var String consumerSecret from twitter app
    **/
    private $consumerSecret = null;

    /**
     * @var String[] list of allowed twitter ids
    **/
    private $whitelist = [];

    /**
     * @var Array config values
    **/
    private $config = [];

    public function __construct($config = [])
    {
        $this->config = $config;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->readConfig();
        $this->handleTwitterCallback();
    }

    public function getLoginUser()
    {
        $this->logIn();
        //var_dump($this->whitelist);exit;
        if ($this->isOnWhitelist()) {
            return $_SESSION['twitter'];
        }
        return [];
    }

    public function isLoggedIn()
    {
        return
            $this->isLoggedInOnTwitter()
            && $this->isOnWhitelist();
    }

    private function isLoggedInOnTwitter()
    {
        return
            isset($_SESSION['twitter']['name'])
            && isset($_SESSION['twitter']['id']);
    }

    private function logIn()
    {
        if ($this->isLoggedInOnTwitter()) {
            return;
        }
        $connection               = new TwitterOAuth(
            $this->consumerKey,
            $this->consumerSecret
        );

        $_SESSION["returnUrl"] =
            (isset($_SERVER['HTTPS']) ? "https" : "http")
            . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $requestToken             = $connection->oauth(
            'oauth/request_token',
            array('oauth_callback' => $_SESSION["returnUrl"])
        );
        $_SESSION['twitter_oauth'] = $requestToken;
        $url                      = $connection->url(
            'oauth/authorize',
            array('oauth_token' => $requestToken['oauth_token'])
        );
        //redirect to twitter
        header("Location: " . $url);
        echo "redirecting<script>location.href=location.href</script>redirecting to " . $url;
        exit;
    }

    private function isOnWhitelist()
    {
        return
            isset($_SESSION['twitter']['id'])
            && is_array($this->whitelist)
            && in_array($_SESSION['twitter']['id'], $this->whitelist);
    }

    private function handleTwitterCallback()
    {
        if (!isset($_REQUEST['oauth_token'])) {
            return;
        }

        $requestToken = $_SESSION['twitter_oauth'];

        if ($requestToken['oauth_token'] !== $_REQUEST['oauth_token']) {
            throw new AuthentificationException("login did not work");
        }


        $connection = new TwitterOAuth(
            $this->consumerKey,
            $this->consumerSecret,
            $requestToken['oauth_token'],
            $requestToken['oauth_token_secret']
        );


        $accessToken = $connection->oauth(
            "oauth/access_token",
            ["oauth_verifier" => $_REQUEST['oauth_verifier']]
        );

        if (!isset($accessToken['user_id'])
            || !isset($accessToken['screen_name'])
        ) {
            throw new AuthentificationException("login did not work");
        }


        $_SESSION['twitter']['name'] = $accessToken['screen_name'];
        $_SESSION['twitter']['id'] = $accessToken['user_id'];

        //return to initial url
        header("Location: " . $_SESSION["returnUrl"]);
        echo "redirecting<script>location.href=location.href</script>redirecting to " . $_SESSION["returnUrl"];
        exit;
    }

    private function readConfig()
    {
        foreach (['CONSUMER_KEY','CONSUMER_SECRET','WHITELIST'] as $configKey) {
            if (empty($this->config[$configKey])) {
                throw new ConfigException($configKey . " is missing in config");
            }
        }

        $this->consumerKey = $this->config['CONSUMER_KEY'];
        $this->consumerSecret = $this->config['CONSUMER_SECRET'];
        $this->whitelist = explode(",", $this->config['WHITELIST']);
    }
}
