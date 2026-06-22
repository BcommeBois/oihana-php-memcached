<?php

namespace tests\oihana\memcached\enums ;

use oihana\memcached\enums\MemcachedStats ;

use PHPUnit\Framework\Attributes\CoversClass ;
use PHPUnit\Framework\TestCase ;

#[CoversClass( MemcachedStats::class )]
class MemcachedStatsTest extends TestCase
{
    public function testGetGroupsReturnsTheCategoryNames() : void
    {
        $this->assertSame
        (
            [ 'general' , 'connections' , 'commands' , 'network' , 'memory' , 'other' ] ,
            MemcachedStats::getGroups()
        ) ;
    }

    public function testGroupByCategoryMapsEachGroupToItsStatKeys() : void
    {
        $groups = MemcachedStats::groupByCategory() ;

        $this->assertSame( MemcachedStats::getGroups() , array_keys( $groups ) ) ;

        $this->assertContains( MemcachedStats::UPTIME  , $groups[ 'general'  ] ) ;
        $this->assertContains( MemcachedStats::GET_HITS , $groups[ 'commands' ] ) ;
        $this->assertContains( MemcachedStats::BYTES   , $groups[ 'memory'   ] ) ;

        foreach ( $groups as $keys )
        {
            $this->assertNotEmpty( $keys ) ;
            foreach ( $keys as $key )
            {
                $this->assertIsString( $key ) ;
            }
        }
    }
}
