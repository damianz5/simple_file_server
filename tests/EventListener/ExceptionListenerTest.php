<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\ExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionListenerTest extends TestCase
{
    /**
     * @dataProvider getExceptionProvider
     */
    public function testListenerOn404Exception(
        string $errorMessage,
        \Exception $exception,
        \Exception $eventException = null
    ): void
    {
        $event = $this->createEvent($exception);

        $listener = new ExceptionListener();
        $listener->onKernelException($event);

        $contentResponse = json_decode(
            $event->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR
        );

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
        $this->assertEquals('error', $contentResponse['status']);
        $this->assertEquals($errorMessage, $contentResponse['message']);

        if ($eventException) {
            $this->assertSame($eventException, $event->getThrowable()->getPrevious());
        }
    }

    public function getExceptionProvider()
    {
        return [
            [
                'An error occurs: The file / could not be accessed with code: 0',
                new AccessDeniedException('/'),
            ],
            [
                'An error occurs: No route found for "GET /test" with code: 404',
                new NotFoundHttpException('No route found for "GET /test"', new ResourceNotFoundException(), 404),
            ],
            [
                'An error occurs: random with code: 0',
                new \LogicException('random', 0, $e = new AccessDeniedException('/test')),
                $e,
            ],
            [
                'An error occurs: page not found with code: 404',
                new NotFoundHttpException('page not found', $e, 404),
            ],
            [
                'An error occurs: random with code: 404',
                new \LogicException('random', 404, $e = new AccessDeniedException('embed')),
                $e,
            ],
            [
                'An error occurs: random with code: 0',
                new \LogicException('random', 0, $e = new AccessDeniedException('embed')),
                $e,
            ],
            [
                'An error occurs: The file random could not be accessed with code: 0',
                new AccessDeniedException('random'),
            ],
        ];
    }

    private function createEvent(\Exception $exception, $kernel = null): ExceptionEvent
    {
        if (null === $kernel) {
            $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        }

        return new ExceptionEvent($kernel, Request::create('/'), HttpKernelInterface::MAIN_REQUEST, $exception);
    }
}
