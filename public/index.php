<?php

header("Cache-Control: public, max-age=0, no-cache");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

function throwAllErrorsAsExceptionsErrorHandler($errno, $errstr, $errfile, $errline)
{
    throw new Exception($errstr);
}
set_error_handler("throwAllErrorsAsExceptionsErrorHandler");


require __DIR__ . '/../vendor/autoload.php';

use Itsmethemojo\Authentification\TwitterExtended;
use Itsmethemojo\Authentification\Redirect;

$app = new \Slim\App();

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
            $twitter->getLoginUser();
            return $response->withRedirect($redirectTarget);
        } catch (Exception $ex) {
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
