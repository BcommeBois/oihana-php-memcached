<?php

use DI\Container;

use oihana\enums\IniOptions;
use oihana\memcached\enums\MemcachedConfig as Config ;
use oihana\memcached\enums\MemcachedDefinition as Definition ;

use function oihana\init\initConfig ;
use function oihana\init\initDefaultTimezone;
use function oihana\init\initErrors;
use function oihana\init\initMemoryLimit;

return
[
    Definition::CONFIG
        => fn( Container $container )
        => initConfig
        (
            basePath : $container->get( Definition::CONFIG_PATH )  ,
            init     : function( array $config ) use ( $container ) :array
            {
                initDefaultTimezone (  $config[ Config::TIMEZONE         ] ?? null ) ;
                initErrors          (        $config[ Config::ERRORS           ] ?? null , $container->get( Definition::APP_PATH ) ) ;
                initMemoryLimit     ( $config[ IniOptions::MEMORY_LIMIT ] ?? null ) ;
                return $config ;
            }
        )
];
