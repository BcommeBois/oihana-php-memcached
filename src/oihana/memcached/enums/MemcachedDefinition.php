<?php

namespace oihana\memcached\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * Enumeration of memcached command definitions keys.
 *
 * @package oihana\memcached\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class MemcachedDefinition
{
    use ConstantsTrait ;

    const string APP_PATH    = 'appPath'    ;
    const string COMMAND     = 'command'    ;
    const string COMMANDS    = 'commands'   ;
    const string CONFIG      = 'config'     ;
    const string CONFIG_PATH = 'configPath' ;
    const string MEMCACHED   = 'memcached'  ;
}