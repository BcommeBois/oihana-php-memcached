<?php

namespace oihana\memcached\controllers;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Memcached;

use oihana\enums\http\HttpStatusCode;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use oihana\controllers\Controller;
use oihana\controllers\enums\ControllerParam;
use oihana\controllers\enums\Skin;
use oihana\controllers\traits\prepare\PrepareSkin;

use oihana\memcached\traits\MemcachedTrait;
use ReflectionException;

/**
 * The memcached controller class.
 *
 * Host-application glue: it extends the Slim/PSR-7 `Controller` from `oihana/php-controllers`,
 * which requires a fully wired Slim application (response factory, router, `app` reference).
 * That environment only exists in a consuming project, so the controller is exercised there rather than in this
 * library's isolated suite — hence it is excluded from coverage here.
 *
 * @codeCoverageIgnore
 */
class MemcachedController extends Controller
{
    /**
     * Creates a new MemcachedController instance.
     * @param Container $container The DI Container reference.
     * @param ?Memcached $memcached The memcached client reference.
     * @param array $init The init object.
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __construct( Container $container , ?Memcached $memcached , array $init = [] )
    {
        parent::__construct( $container , $init );
        $this->initializeSkins( $init ) ;
        $this->memcached = $memcached;
    }

    use MemcachedTrait ,
        PrepareSkin ;

    /**
     * Flush the memcached cache.
     * @param Request $request
     * @param Response $response
     * @return ?Response
     */
    public function flush( Request $request , Response $response ) : ?Response
    {
        try
        {
            $code = $this->memcachedFlush() ;

            if( $code == Memcached::RES_SUCCESS )
            {
                return $this->success( $request , $response , true ) ;
            }

            return $this->fail( $request , $response , HttpStatusCode::INTERNAL_SERVER_ERROR , $this->memcached->getResultMessage() ) ;
        }
        catch ( Exception $e )
        {
            return $this->fail( $request , $response , HttpStatusCode::INTERNAL_SERVER_ERROR , $e->getMessage() ) ;
        }
    }

    /**
     * Returns the statistics of the memcached cache.
     * @param Request $request
     * @param Response $response
     * @param array $init
     * @return ?Response
     */
    public function stats( Request $request , Response $response , array $init = [] ) : ?Response
    {
        try
        {
            $params = $init[ ControllerParam::PARAMS ] ?? [] ;
            $skin   = $this->prepareSkin( $request , $init , $params ) ;
            return $this->success( $request , $response , $this->memcachedStats( $skin == Skin::FULL ) ) ;
        }
        catch ( Exception $e )
        {
            return $this->fail( $request , $response , HttpStatusCode::INTERNAL_SERVER_ERROR , $e->getMessage() ) ;
        }
    }
}

