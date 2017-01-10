<?php
/*
header("Cache-Control: public, max-age=0, no-cache");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);


require __DIR__ .'/../vendor/autoload.php';

//$twitter = new Itsmethemojo\Authentification\Twitter();
//var_dump($twitter->getLoginUser());


$twitter = new Itsmethemojo\Authentification\TwitterExtended();
var_dump($twitter->getLoginUser());
*/


require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App();

$app->get(
    '/status',
    function ($request, $response, $args) {
        $twitter = new Itsmethemojo\Authentification\TwitterExtended();
        if (!$twitter->isLoggedIn()) {
            return $response->withStatus(401)->withJson(array("message" => "not authorized"));
        }
        return $response->withJson(array("message" => "authorized"));
    }
);

//TODO read redirect url to send back after login

$app->get(
    '/login',
    function ($request, $response, $args) {
        $twitter = new Itsmethemojo\Authentification\TwitterExtended();
        if ($twitter->getLoginUser()["id"] !== "26008379") {
            return $response->withStatus(401)->withJson(array("message" => "not authorized"));
        }
        return $response->withJson(array("message" => "authorized"));
    }
);

$app->run();