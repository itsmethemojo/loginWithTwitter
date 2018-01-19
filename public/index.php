<?php

require __DIR__ . '/../vendor/autoload.php';

use Itsmethemojo\Authentification\TwitterExtended;
use Itsmethemojo\Authentification\Redirect;
use Itsmethemojo\Authentification\ParameterException;
use Itsmethemojo\File\Config;

$iniFilename = 'login';

try {
    $debug = boolval(Config::get($iniFilename, array('DEBUG_MODE'))['DEBUG_MODE']);
} catch (Exception $e) {
    $debug = false;
}

$config = [
    'settings' => [
        'displayErrorDetails' => $debug,
        'iniFileName' => $iniFilename
    ],
];

$app = new \Slim\App($config);

//setup caching
$app->add(new \Slim\HttpCache\Cache());
$container = $app->getContainer();
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};


$app->get(
    '/status',
    function ($request, $response, $args) {
        $twitter = new TwitterExtended($this->get('settings')['iniFileName']);
        if (!$twitter->isLoggedIn()) {
            $output = $response->withStatus(401)->withJson(array("status" => "not authorized"));
            return $this->cache->allowCache($output, 'public', 0);
        }
        $output = $response->withJson(
            array_merge(
                array("status" => "authorized"),
                $twitter->getTokenUserData()
            )
        );
        return $this->cache->allowCache($output, 'public', 0);
    }
);

$app->get(
    '/login',
    function ($request, $response, $args) {
        try {
            $redirectTarget = Redirect::getUrl($request->getParams(), $request->getServerParams());
            $twitter = new TwitterExtended($this->get('settings')['iniFileName']);
            $twitter->doLogin();
            return $response->withRedirect($redirectTarget);
        } catch (ParameterException $ex) {
            $output = $response->withStatus(400)->withJson(
                array(
                    'error' => $ex->getMessage()
                )
            );
            return $this->cache->allowCache($output, 'public', 0);
        }
    }
);

$app->get(
    '/',
    function ($request, $response, $args) {
        $output = $response->write(
            "<pre>\n"
            . "for route documentation open "
            . "https://github.com/itsmethemojo/loginWithTwitter/blob/master/documentation/routes.md"
            . "</pre>"
        );
        return $this->cache->allowCache($output, 'public', 2592000);
    }
);

$app->run();
