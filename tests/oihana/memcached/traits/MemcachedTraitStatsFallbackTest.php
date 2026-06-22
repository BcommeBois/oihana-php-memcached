<?php

namespace tests\oihana\memcached\traits ;

use Memcached ;

use oihana\memcached\traits\MemcachedTrait ;

use PHPUnit\Framework\Attributes\CoversTrait ;
use PHPUnit\Framework\TestCase ;

/**
 * A real {@see Memcached} subclass whose `getStats()` reports nothing — used to
 * reach the defensive fall-backs without mocking the internal extension class
 * (which PHPUnit flags with a notice).
 */
class EmptyStatsMemcached extends Memcached
{
    public function getStats( ?string $type = null ) : array|false
    {
        return [] ;
    }
}

/**
 * Fixture composing {@see MemcachedTrait} around the stubbed client.
 */
class StatsFallbackFixture
{
    use MemcachedTrait ;

    public function __construct( Memcached $memcached )
    {
        $this->initializeMemcached( [ self::MEMCACHED => $memcached ] , null ) ;
    }
}

/**
 * Covers the defensive `0` fall-backs of {@see MemcachedTrait::memcachedHitRatio()}
 * and {@see MemcachedTrait::memcachedUptime()} when the server reports no stats —
 * unreachable through a live server (whose stats are never empty).
 */
#[CoversTrait( MemcachedTrait::class )]
class MemcachedTraitStatsFallbackTest extends TestCase
{
    public function testHitRatioAndUptimeFallBackToZeroWhenStatsAreEmpty() : void
    {
        $fixture = new StatsFallbackFixture( new EmptyStatsMemcached() ) ;

        $this->assertSame( 0.0 , $fixture->memcachedHitRatio() ) ;
        $this->assertSame( 0   , $fixture->memcachedUptime()   ) ;
    }
}
