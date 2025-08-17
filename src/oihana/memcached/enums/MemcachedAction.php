<?php

namespace oihana\memcached\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * Enumeration of the memcached actions.
 *
 * @package oihana\memcached\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class MemcachedAction
{
    use ConstantsTrait ;

    /**
     * The flush action
     */
    public const string FLUSH  = 'flush' ;

    /**
     * The stats action
     */
    public const string STATS = 'stats' ;
}