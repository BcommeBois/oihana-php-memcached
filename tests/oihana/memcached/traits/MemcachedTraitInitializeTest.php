<?php

namespace tests\oihana\memcached\traits;

use Memcached;

use oihana\memcached\traits\MemcachedTrait;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Named fixture exposing the trait's protected `initializeMemcached`
 * method as public so the test can drive it without reflection.
 */
class MemcachedInitFixture
{
    use MemcachedTrait { initializeMemcached as public ; }
}

#[CoversClass( MemcachedInitFixture::class )]
#[RequiresPhpExtension( 'memcached' )]
class MemcachedTraitInitializeTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWiresInstanceFromInitArrayUnderDefaultKey() :void
    {
        $fixture   = new MemcachedInitFixture() ;
        $memcached = new Memcached() ;

        $fixture->initializeMemcached( [ MemcachedInitFixture::MEMCACHED => $memcached ] , null ) ;

        $this->assertSame( $memcached , $fixture->memcached ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testStaysNullWhenInitArrayIsEmpty() :void
    {
        $fixture = new MemcachedInitFixture() ;

        $fixture->initializeMemcached( [] , null ) ;

        $this->assertNull( $fixture->memcached ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testStaysNullWhenInitKeyIsMissing() :void
    {
        $fixture = new MemcachedInitFixture() ;

        $fixture->initializeMemcached( [ 'other' => 'foo' ] , null ) ;

        $this->assertNull( $fixture->memcached ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testResolvesContainerServiceIdFromInitArray() :void
    {
        $fixture   = new MemcachedInitFixture() ;
        $memcached = new Memcached() ;

        $container = $this->createMock( ContainerInterface::class ) ;
        $container->method( 'has' )->with( 'cache.memcached' )->willReturn( true ) ;
        $container->method( 'get' )->with( 'cache.memcached' )->willReturn( $memcached ) ;

        $fixture->initializeMemcached
        (
            [ MemcachedInitFixture::MEMCACHED => 'cache.memcached' ] ,
            $container
        ) ;

        $this->assertSame( $memcached , $fixture->memcached ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testStaysNullWhenContainerHasReturnsFalse() :void
    {
        $fixture = new MemcachedInitFixture() ;

        $container = $this->createStub( ContainerInterface::class ) ;
        $container->method( 'has' )->willReturn( false ) ;

        $fixture->initializeMemcached
        (
            [ MemcachedInitFixture::MEMCACHED => 'cache.memcached' ] ,
            $container
        ) ;

        $this->assertNull( $fixture->memcached ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsSelfForFluentChaining() :void
    {
        $fixture = new MemcachedInitFixture() ;

        $this->assertSame( $fixture , $fixture->initializeMemcached( [] , null ) ) ;
        $this->assertSame
        (
            $fixture ,
            $fixture->initializeMemcached
            (
                [ MemcachedInitFixture::MEMCACHED => new Memcached() ] ,
                null
            )
        ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testIgnoresContainerWhenInitArrayCarriesInstanceDirectly() :void
    {
        $fixture   = new MemcachedInitFixture() ;
        $memcached = new Memcached() ;

        $container = $this->createMock( ContainerInterface::class ) ;
        $container->expects( $this->never() )->method( 'has' ) ;
        $container->expects( $this->never() )->method( 'get' ) ;

        $fixture->initializeMemcached
        (
            [ MemcachedInitFixture::MEMCACHED => $memcached ] ,
            $container
        ) ;

        $this->assertSame( $memcached , $fixture->memcached ) ;
    }
}