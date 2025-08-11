<?php

namespace oihana\memcached\enums;

use oihana\reflections\traits\ConstantsTrait;

/**
 * Enumeration of memcached command config keys.
 *
 * @package oihana\memcached\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class MemcachedConfig
{
    use ConstantsTrait ;

    public const string COMMAND   = 'command'   ;
    public const string ERRORS    = 'errors'    ;
    public const string HOST      = 'host'      ;
    public const string MEMCACHED = 'memcached' ;
    public const string PORT      = 'port'      ;
    public const string TIMEZONE  = 'timezone'  ;
}