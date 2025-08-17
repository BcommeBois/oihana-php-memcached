<?php

namespace tests\oihana\memcached\helpers;

use DI\DependencyException;
use DI\NotFoundException;
use PHPUnit\Framework\TestCase;

use DI\Container;
use MatthiasMullie\Scrapbook\KeyValueStore;
use MatthiasMullie\Scrapbook\Psr16\SimpleCache;

use oihana\memcached\enums\MemcachedDefinition;

use stdClass;
use function oihana\memcached\helpers\cacheCollection;

class CacheCollectionTest extends TestCase
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testReturnsSimpleCacheWhenDefinitionIsValid(): void
    {
        $collectionName = 'users';

        // KeyValueStore Mock
        $store = $this->createMock(KeyValueStore::class ) ;

        $store->expects    ( $this->once() )
              ->method     ('getCollection' )
              ->with       ( $collectionName )
              ->willReturn ( $store ); // Scrapbook returns a KeyValueStore

        // Mock du container
        $container = $this->createMock(Container::class);

        $container->expects($this->once())
            ->method('has')
            ->with(MemcachedDefinition::CACHE_MEMCACHED)
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->with(MemcachedDefinition::CACHE_MEMCACHED)
            ->willReturn($store);

        $cache = cacheCollection($container, $collectionName);

        $this->assertInstanceOf(SimpleCache::class, $cache);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testReturnsNullWhenDefinitionDoesNotExist(): void
    {
        $container = $this->createMock( Container::class ) ;

        $container->expects($this->once())
                  ->method('has')
                  ->willReturn(false);

        $cache = cacheCollection($container, 'test');
        $this->assertNull($cache);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testReturnsNullWhenDefinitionIsNotKeyValueStore(): void
    {
        $container = $this->createMock( Container::class ) ;
        $container->expects($this->once())
                  ->method('has')
                  ->willReturn(true);

        $container->expects($this->once())
                  ->method('get')
                  ->willReturn(new stdClass()) ; // not a KeyValueStore

        $cache = cacheCollection( $container , 'invalid' ) ;
        $this->assertNull( $cache ) ;
    }
}