<?php

namespace tests\oihana\memcached\rateLimit ;

use Memcached ;
use UnexpectedValueException ;

use oihana\memcached\rateLimit\MemcachedRateLimitStore ;

use PHPUnit\Framework\Attributes\CoversClass ;
use PHPUnit\Framework\Attributes\RequiresPhpExtension ;
use PHPUnit\Framework\TestCase ;

use Psr\Container\ContainerInterface ;

#[CoversClass( MemcachedRateLimitStore::class )]
#[RequiresPhpExtension( 'memcached' )]
class MemcachedRateLimitStoreTest extends TestCase
{
    private function newMemcachedMock() :Memcached
    {
        return $this->createMock( Memcached::class ) ;
    }

    private function newMemcachedStub() :Memcached
    {
        return $this->createStub( Memcached::class ) ;
    }

    public function testConstructorAcceptsADirectMemcachedInstance() :void
    {
        $memcached = $this->newMemcachedStub() ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $this->assertSame( $memcached , $store->memcached ) ;
    }

    public function testConstructorThrowsWhenInitArrayIsEmpty() :void
    {
        $this->expectException( UnexpectedValueException::class ) ;

        new MemcachedRateLimitStore( [] ) ;
    }

    public function testConstructorResolvesContainerServiceId() :void
    {
        $memcached = $this->newMemcachedStub() ;

        $container = $this->createMock( ContainerInterface::class ) ;
        $container->method( 'has' )->with( 'cache.memcached' )->willReturn( true ) ;
        $container->method( 'get' )->with( 'cache.memcached' )->willReturn( $memcached ) ;

        $store = new MemcachedRateLimitStore
        (
            [ MemcachedRateLimitStore::MEMCACHED => 'cache.memcached' ] ,
            $container ,
        ) ;

        $this->assertSame( $memcached , $store->memcached ) ;
    }

    public function testIncrementReturnsValueDirectlyWhenCounterExists() :void
    {
        $memcached = $this->newMemcachedMock() ;

        // Steady-state path: increment succeeds on the first call.
        $memcached->expects( $this->once() )
                  ->method( 'increment' )
                  ->with( 'rl:k:1000' )
                  ->willReturn( 4 ) ;

        $memcached->expects( $this->never() )->method( 'add' ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $this->assertSame( 4 , $store->increment( 'rl:k:1000' , 60 ) ) ;
    }

    public function testIncrementInitializesCounterAtOneWithTtlWhenAbsent() :void
    {
        $memcached = $this->newMemcachedMock() ;

        // First increment misses (counter not yet created).
        $memcached->expects( $this->once() )
                  ->method( 'increment' )
                  ->with( 'rl:k:1000' )
                  ->willReturn( false ) ;

        // add() with initial value 1 and TTL = $window seeds the cell atomically.
        $memcached->expects( $this->once() )
                  ->method( 'add' )
                  ->with( 'rl:k:1000' , 1 , 60 )
                  ->willReturn( true ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $this->assertSame( 1 , $store->increment( 'rl:k:1000' , 60 ) ) ;
    }

    public function testIncrementFallsBackToSecondIncrementWhenAddRaceIsLost() :void
    {
        $memcached = $this->newMemcachedMock() ;

        // increment() misses on the first call, then succeeds after the race.
        $memcached->expects( $this->exactly( 2 ) )
                  ->method( 'increment' )
                  ->with( 'rl:k:1000' )
                  ->willReturnOnConsecutiveCalls( false , 2 ) ;

        // add() loses the race (another worker created the cell in between).
        $memcached->expects( $this->once() )
                  ->method( 'add' )
                  ->with( 'rl:k:1000' , 1 , 60 )
                  ->willReturn( false ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $this->assertSame( 2 , $store->increment( 'rl:k:1000' , 60 ) ) ;
    }

    public function testIncrementReturnsOneWhenSecondIncrementAlsoFails() :void
    {
        // Defensive — under extreme failure scenarios the cell may still be
        // unreachable after the add race. We never propagate `false`.
        $memcached = $this->newMemcachedStub() ;

        $memcached->method( 'increment' )->willReturn( false ) ;
        $memcached->method( 'add' )->willReturn( false ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $this->assertSame( 1 , $store->increment( 'rl:k:1000' , 60 ) ) ;
    }

    public function testIncrementHonoursTheWindowArgumentInAdd() :void
    {
        $memcached = $this->newMemcachedMock() ;

        $memcached->method( 'increment' )->willReturn( false ) ;
        $memcached->expects( $this->once() )
                  ->method( 'add' )
                  ->with( $this->anything() , 1 , 300 )
                  ->willReturn( true ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $store->increment( 'rl:k:42' , 300 ) ;
    }

    public function testIncrementDoesNotExtendTtlOnSubsequentCalls() :void
    {
        // The Memcached increment() command does not touch the TTL — we mirror
        // this contract by NEVER calling add() / touch() on the hot path.
        $memcached = $this->newMemcachedMock() ;

        $memcached->expects( $this->exactly( 3 ) )
                  ->method( 'increment' )
                  ->with( 'rl:k:1000' )
                  ->willReturnOnConsecutiveCalls( 2 , 3 , 4 ) ;

        $memcached->expects( $this->never() )->method( 'add' ) ;
        $memcached->expects( $this->never() )->method( 'touch' ) ;
        $memcached->expects( $this->never() )->method( 'set' ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $store->increment( 'rl:k:1000' , 60 ) ;
        $store->increment( 'rl:k:1000' , 60 ) ;
        $store->increment( 'rl:k:1000' , 60 ) ;
    }

    public function testDistinctKeysAreIndependent() :void
    {
        $memcached = $this->newMemcachedStub() ;

        $memcached->method( 'increment' )->willReturnCallback
        (
            fn( string $key ) :int|false => match ( $key )
            {
                'rl:a:1000' => 7  ,
                'rl:b:1000' => 12 ,
                default     => false ,
            }
        ) ;

        $store = new MemcachedRateLimitStore( [ MemcachedRateLimitStore::MEMCACHED => $memcached ] ) ;

        $this->assertSame( 7  , $store->increment( 'rl:a:1000' , 60 ) ) ;
        $this->assertSame( 12 , $store->increment( 'rl:b:1000' , 60 ) ) ;
    }
}
