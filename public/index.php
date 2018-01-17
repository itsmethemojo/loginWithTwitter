<?php

header("Cache-Control: public, max-age=0, no-cache");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

//TODO use slim integrated caching

require __DIR__ . '/../vendor/autoload.php';

use Itsmethemojo\Authentification\TwitterExtended;
use Itsmethemojo\Authentification\Redirect;
use Itsmethemojo\File\Config;

//(new \Itsmethemojo\Error\Handler())->throwAllErrorsAsExceptions();

$config = [
    'settings' => [
        'displayErrorDetails' => Config::get('twitter', array('debug'))['debug'] ? 'true' : 'false'
    ],
];

$app = new \Slim\App($config);

$app->get(
    '/status',
    function ($request, $response, $args) {
        try {
            $twitter = new TwitterExtended();
            if (!$twitter->isLoggedIn()) {
                return $response->withStatus(401)->withJson(array("message" => "not authorized"));
            }
            return $response->withJson(array("message" => "authorized"));
        } catch (Exception $ex) {
            if ($this->get('settings')['displayErrorDetails']) {
                throw $ex;
            }

            return $response->withStatus(500)->withJson(
                array(
                    'error' => $ex->getMessage()
                )
            );
        }
    }
);

$app->get(
    '/login',
    function ($request, $response, $args) {
        try {
            $redirectTarget = Redirect::getUrl($request->getParams(), $request->getServerParams());
            $twitter = new TwitterExtended();
            $twitter->doLogin();
            return $response->withRedirect($redirectTarget);
        } catch (Exception $ex) {
            if ($this->get('settings')['displayErrorDetails']) {
                throw $ex;
            }
            //TODO distinguish parameter missing with 400
            return $response->withStatus(500)->withJson(
                array(
                    'error' => $ex->getMessage()
                )
            );
        }
    }
);

$app->get(
    '/',
    function ($request, $response, $args) {
        return $response->write(
            "<pre>\n" .
            "LoginWithTwitter API\n\n" .
            "  avialable methods:\n\n" .
            "    [1] /status\n" .
            "    [2] /login\n\n" .
            "  documentation\n\n" .
            "    /status\n\n" .
            "      method: GET\n" .
            "      parameters: -\n\n" .
            "    /login\n\n" .
            "      method: GET\n" .
            "      parameters:\n" .
            "        \"redirect\" (optional) target url to redirect after the login\n" .
            "                   if not given HTTP_REFERER will be used to redirect\n" .
            "</pre>"
        );
    }
);

$app->run();
