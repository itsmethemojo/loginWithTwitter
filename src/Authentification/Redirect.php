<?php

namespace Itsmethemojo\Authentification;

class Redirect
{
    const REDIRECT_URL_KEY = 'redirect';
    const REFERER_SERVER_KEY = 'HTTP_REFERER';

    public static function getUrl($urlParameters, $serverParameters)
    {
        if (key_exists(self::REDIRECT_URL_KEY, $urlParameters)
            && self::isUrl($urlParameters[self::REDIRECT_URL_KEY])
        ) {
            return $urlParameters[self::REDIRECT_URL_KEY];
        }
        if (key_exists(self::REDIRECT_URL_KEY, $serverParameters)
            && self::isUrl($serverParameters[self::REFERER_SERVER_KEY])
        ) {
            return $serverParameters[self::REFERER_SERVER_KEY];
        }
        throw new ParameterException(
            'no redirect target in HTTP_REFERER or \''
            . self::REDIRECT_URL_KEY
            . '\' url parameter'
        );
    }

    private static function isUrl($string)
    {
        return substr($string, 0, 4) === "http"
               && filter_var($string, FILTER_VALIDATE_URL);
    }
}
