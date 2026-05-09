<?php

namespace tests\oihana\memcached\helpers;

use Memcached;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use Psr\Container\NotFoundExceptionInterface;
use function oihana\memcached\helpers\getMemcached;

#[CoversFunction( 'oihana\memcached\helpers\getMemcached' )]
#[RequiresPhpExtension( 'memcached' )]
class GetMemcachedTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsInstanceWhenPassedDirectly() :void
    {
        $memcached = new Memcached() ;

        $this->assertSame( $memcached , getMemcached( $memcached ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultWhenDefinitionIsNull() :void
    {
        $default = new Memcached() ;

        $this->assertSame( $default , getMemcached( null , null , 'memcached' , $default ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsNullWhenDefinitionIsNullAndNoDefault() :void
    {
        $this->assertNull( getMemcached() ) ;
    }

    public function testResolvesFromArrayUsingDefaultKey() :void
    {
        $memcached = new Memcached() ;

        $this->assertSame( $memcached , getMemcached( [ 'memcached' => $memcached ] ) ) ;
    }

    public function testResolvesFromArrayUsingCustomKey() :void
    {
        $memcached = new Memcached() ;

        $this->assertSame( $memcached , getMemcached( [ 'cache' => $memcached ] , null , 'cache' ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultWhenArrayKeyIsMissing() :void
    {
        $default = new Memcached() ;

        $this->assertSame( $default , getMemcached( [ 'other' => 'foo' ] , null , 'memcached' , $default ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testResolvesFromContainerUsingStringServiceId() :void
    {
        $memcached = new Memcached() ;

        $container = $this->createMock( ContainerInterface::class ) ;
        $container->method( 'has' )->with( 'cache.memcached' )->willReturn( true ) ;
        $container->method( 'get' )->with( 'cache.memcached' )->willReturn( $memcached ) ;

        $this->assertSame( $memcached , getMemcached( 'cache.memcached' , $container ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultWhenContainerHasReturnsFalse() :void
    {
        $default = new Memcached() ;

        $container = $this->createStub( ContainerInterface::class ) ;
        $container->method( 'has' )->willReturn( false ) ;

        $this->assertSame( $default , getMemcached( 'cache.memcached' , $container , 'memcached' , $default ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultWhenContainerReturnsNonMemcached() :void
    {
        $default = new Memcached() ;

        $container = $this->createStub( ContainerInterface::class ) ;
        $container->method( 'has' )->willReturn( true ) ;
        $container->method( 'get' )->willReturn( 'not a Memcached' ) ;

        $this->assertSame( $default , getMemcached( 'cache.memcached' , $container , 'memcached' , $default ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsDefaultWhenStringDefinitionIsEmpty() :void
    {
        $default = new Memcached() ;

        $this->assertSame( $default , getMemcached( '' , null , 'memcached' , $default ) ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testArrayResolvesNestedStringServiceId() :void
    {
        $memcached = new Memcached() ;

        $container = $this->createMock( ContainerInterface::class ) ;
        $container->method( 'has' )->with( 'cache.memcached' )->willReturn( true ) ;
        $container->method( 'get' )->with( 'cache.memcached' )->willReturn( $memcached ) ;

        $this->assertSame( $memcached , getMemcached( [ 'memcached' => 'cache.memcached' ] , $container ) ) ;
    }
}