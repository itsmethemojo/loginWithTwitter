<?php

namespace Itsmethemojo\Authentification;

use Abraham\TwitterOAuth\TwitterOAuth;
use Itsmethemojo\File\ConfigReader;
use Exception;

class Twitter
{
    private $consumerKey    = null;
    private $consumerSecret = null;

    public function getLoginUser()
    {
        $this->logIn();
        return $_SESSION['twitter'];
    }

    public function isLoggedIn()
    {
        $this->init();
        return
            isset($_SESSION['twitter']['name'])
            && isset($_SESSION['twitter']['id']);
    }

    private function logIn()
    {
        $this->init();
        if ($this->isLoggedIn()) {
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

    private function init()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if ($this->consumerKey === null || $this->consumerSecret === null) {
            $this->readConfig();
        }
        $this->handleTwitterCallback();
    }

    private function handleTwitterCallback()
    {
        if (!isset($_REQUEST['oauth_token'])) {
            return;
        }

        $requestToken = $_SESSION['twitter_oauth'];

        if ($requestToken['oauth_token'] !== $_REQUEST['oauth_token']) {
            throw new Exception("login did not work");
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
            throw new Exception("login did not work");
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
        $config               = ConfigReader::get(
            'twitter',
            array('consumerKey', 'consumerSecret')
        );
        $this->consumerKey    = $config['consumerKey'];
        $this->consumerSecret = $config['consumerSecret'];
    }
}
