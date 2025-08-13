<?php

namespace oihana\memcached\options;

use oihana\options\Option;

/**
 * Enumeration of the MemCached command option.
 *
 * @package oihana\memcached\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class MemcachedOption extends Option
{
    /**
     * The 'flush' option.
     * @var string
     */
    public const string FLUSH = 'flush';

    /**
     * The 'verbose' option.
     * @var string
     */
    public const string VERBOSE = 'verbose';
}