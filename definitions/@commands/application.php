<?php

use Psr\Container\ContainerInterface ;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

use oihana\memcached\enums\MemcachedDefinition as Definition;

return
[
    Application::class => function( ContainerInterface $container ) :Application
    {
        $application = new Application() ;
        if( $container->has( Definition::COMMANDS ) )
        {
            $definitions = $container->get( Definition::COMMANDS ) ;
            if( is_array( $definitions ) && count( $definitions ) > 0 )
            {
                foreach ( $definitions as $definition )
                {
                    if( $container->has( $definition ) )
                    {
                        $command = $container->get( $definition ) ;
                        if( $command instanceof Command )
                        {
                            $application->add( $command );
                        }
                    }
                }
            }
        }
        return $application ;
    }
];
