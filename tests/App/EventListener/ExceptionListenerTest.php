<?php

namespace Tests\App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getExceptionProvider
     */
    public function testListenerOn404Exception($errorMessage, \Exception $exception, \Exception $eventException = null)
    {
        $event = $this->createEvent($exception);

        $listener = new \App\EventListener\ExceptionListener();
        $listener->onKernelException($event);

        $contentResponse = json_decode($event->getResponse()->getContent(), true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $event->getResponse());
        $this->assertEquals('error', $contentResponse['status']);
        $this->assertEquals($errorMessage, $contentResponse['message']);

        if ($eventException) {
            $this->assertSame(null === $eventException ? $exception : $eventException, $event->getException()->getPrevious());
        }
    }

    public function getExceptionProvider()
    {
        return [
            [
                'An error occurs: Access Denied. with code: 403',
                new AccessDeniedException(),
            ],
            [
                'An error occurs: No route found for "GET /test" with code: 404',
                new NotFoundHttpException('No route found for "GET /test"', new ResourceNotFoundException(), 404),
            ],
            [
                'An error occurs: random with code: 0',
                new \LogicException('random', 0, $e = new AccessDeniedException()),
                $e,
            ],
            [
                'An error occurs: page not found with code: 404',
                new NotFoundHttpException('page not found', $e, 404),
            ],
            [
                'An error occurs: random with code: 404',
                new \LogicException('random', 404, $e = new AccessDeniedException('embed', new AccessDeniedException())),
                $e,
            ],
            [
                'An error occurs: random with code: 0',
                new \LogicException('random', 0, $e = new AccessDeniedException('embed', new AuthenticationException())),
                $e,
            ],
            [
                'An error occurs: random with code: 403',
                new AccessDeniedException('random', new \LogicException()),
            ],
        ];
    }

    private function createEvent(\Exception $exception, $kernel = null)
    {
        if (null === $kernel) {
            $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        }

        return new GetResponseForExceptionEvent($kernel, Request::create('/'), HttpKernelInterface::MASTER_REQUEST, $exception);
    }
}
