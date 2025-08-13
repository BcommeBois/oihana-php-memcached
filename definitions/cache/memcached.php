<?php

use Psr\Container\ContainerInterface;
use oihana\memcached\enums\MemcachedConfig as Config;
use oihana\memcached\enums\MemcachedDefinition as Definitions;

return
[
    Definitions::MEMCACHED => function( ContainerInterface $container ) : Memcached
    {
        $host =
        $port =  null ;

        if( $container->has( Definitions::CONFIG ) )
        {
            $config = $container->get( Definitions::CONFIG )[ Config::MEMCACHED ] ?? null ;
            if( isset( $config ) )
            {
                $host = $config[ Config::HOST ] ?? $host ;
                $port = $config[ Config::PORT ] ?? $port ;
            }
        }

        $client = new Memcached() ;

        $client->addServer( $host ?? 'localhost' , $port ?? 11211 ) ;

        return $client ;
    }
];
