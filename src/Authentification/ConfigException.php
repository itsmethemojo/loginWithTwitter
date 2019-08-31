<?php

namespace Itsmethemojo\Authentification;

use Exception;

class ConfigException extends Exception
{
    public function __construct(
        $key = "",
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct("missing key \"". $key ."\" in config", $code, $previous);
    }
}
