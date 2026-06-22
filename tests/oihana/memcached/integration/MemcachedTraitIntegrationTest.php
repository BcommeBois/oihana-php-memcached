<?php

namespace tests\oihana\memcached\integration ;

use Memcached ;

use oihana\memcached\traits\MemcachedInfoTrait ;
use oihana\memcached\traits\MemcachedInitTrait ;
use oihana\memcached\traits\MemcachedTrait ;

use org\schema\ItemList ;

use PHPUnit\Framework\Attributes\CoversTrait ;
use PHPUnit\Framework\Attributes\Group ;

/**
 * Minimal class composing {@see MemcachedTrait} around a live client.
 */
class MemcachedTraitFixture
{
    use MemcachedTrait ;

    public function __construct( Memcached $memcached )
    {
        $this->initializeMemcached( [ self::MEMCACHED => $memcached ] , null ) ;
    }
}

/**
 * Live coverage of {@see MemcachedTrait} (and the {@see MemcachedInfoTrait} /
 * {@see MemcachedInitTrait} it composes) against a real Memcached server.
 */
#[Group( 'integration' )]
#[CoversTrait( MemcachedTrait::class )]
#[CoversTrait( MemcachedInfoTrait::class )]
#[CoversTrait( MemcachedInitTrait::class )]
class MemcachedTraitIntegrationTest extends IntegrationTestCase
{
    private function cache() : MemcachedTraitFixture
    {
        return new MemcachedTraitFixture( self::$memcached ) ;
    }

    public function testSetThenGetRoundTripsTheValue() : void
    {
        $cache = $this->cache() ;

        $this->assertTrue( $cache->memcachedSet( 'user:42' , [ 'name' => 'Alice' ] , 60 ) ) ;
        $this->assertSame( [ 'name' => 'Alice' ] , $cache->memcachedGet( 'user:42' ) ) ;
    }

    public function testGetReturnsFalseForAnUnknownKey() : void
    {
        $this->assertFalse( $this->cache()->memcachedGet( 'does:not:exist' ) ) ;
    }

    public function testDeleteRemovesTheKey() : void
    {
        $cache = $this->cache() ;
        $cache->memcachedSet( 'k' , 'v' ) ;

        $this->assertTrue( $cache->memcachedDelete( 'k' ) ) ;
        $this->assertFalse( $cache->memcachedGet( 'k' ) ) ;
    }

    public function testKeyExistsReflectsPresence() : void
    {
        $cache = $this->cache() ;

        $this->assertFalse( $cache->memcachedKeyExists( 'absent' ) ) ;

        $cache->memcachedSet( 'present' , 1 ) ;
        $this->assertTrue( $cache->memcachedKeyExists( 'present' ) ) ;
    }

    public function testIncrementAndDecrementUpdateANumericValue() : void
    {
        $cache = $this->cache() ;
        $cache->memcachedSet( 'counter' , 10 ) ;

        $this->assertSame( 12 , $cache->memcachedIncrement( 'counter' , 2 ) ) ;
        $this->assertSame(  9 , $cache->memcachedDecrement( 'counter' , 3 ) ) ;
    }

    public function testTouchUpdatesTheExpiration() : void
    {
        $cache = $this->cache() ;
        $cache->memcachedSet( 'session' , 'v' , 1 ) ;

        $this->assertTrue( $cache->memcachedTouch( 'session' , 120 ) ) ;
    }

    public function testFlushClearsEverythingAndReturnsSuccess() : void
    {
        $cache = $this->cache() ;
        $cache->memcachedSet( 'x' , 1 ) ;

        $this->assertSame( Memcached::RES_SUCCESS , $cache->memcachedFlush() ) ;
        $this->assertFalse( $cache->memcachedGet( 'x' ) ) ;
    }

    public function testGetAllKeysReturnsAnArray() : void
    {
        $cache = $this->cache() ;
        $cache->memcachedSet( 'a' , 1 ) ;

        $this->assertIsArray( $cache->memcachedGetAllKeys() ) ;
    }

    public function testHitRatioIsAPercentage() : void
    {
        $cache = $this->cache() ;
        $cache->memcachedSet( 'h' , 1 ) ;
        $cache->memcachedGet( 'h' ) ;        // hit
        $cache->memcachedGet( 'h:miss' ) ;   // miss

        $ratio = $cache->memcachedHitRatio() ;

        $this->assertIsFloat( $ratio ) ;
        $this->assertGreaterThanOrEqual( 0.0 , $ratio ) ;
        $this->assertLessThanOrEqual( 100.0 , $ratio ) ;
    }

    public function testUptimeIsANonNegativeInteger() : void
    {
        $this->assertGreaterThanOrEqual( 0 , $this->cache()->memcachedUptime() ) ;
    }

    public function testBasicStatsExposeCacheSizeAndUsage() : void
    {
        $list = $this->cache()->memcachedStats( false ) ;

        $this->assertInstanceOf( ItemList::class , $list ) ;
        $this->assertNotEmpty( $list->itemListElement ) ;
    }

    public function testVerboseStatsExposeTheFullServerBreakdown() : void
    {
        $list = $this->cache()->memcachedStats( true ) ;

        $this->assertInstanceOf( ItemList::class , $list ) ;
        $this->assertNotEmpty( $list->itemListElement ) ;
    }
}
