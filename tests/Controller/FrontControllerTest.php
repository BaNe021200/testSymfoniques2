<?php

namespace Symfony\Component\HttpKernel\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ControllerDoesNotReturnResponseException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

use Psr\Container\ContainerInterface;
use App\Controller\FrontController;

class FrontControllerTest extends TestCase
{

    public function testIndex()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));

        $controller = new FrontController();
        $this->assertSame('', $controller->index());
    }

    public function pas_testIndex()
    {
        $kernel = $this->getHttpKernel(
            new EventDispatcher(),
            (new FrontController())->index
        );

        $response = $kernel->handle(new Request(), HttpKernelInterface::MASTER_REQUEST, true);

        $this->assertSame('', $reponse);
    }
    /**
     * @expectedException \RuntimeException
     */
    public function pas_de_testHandleWhenControllerThrowsAnExceptionAndCatchIsTrue()
    {
        // no-go
    }


    private function getHttpKernel(EventDispatcherInterface $eventDispatcher, $controller = null, RequestStack $requestStack = null, array $arguments = array())
    {
        if (null === $controller) {
            $controller = function () { return new Response('Hello'); };
        }

        $controllerResolver = $this->getMockBuilder(ControllerResolverInterface::class)->getMock();
        $controllerResolver
            ->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($controller));

        $argumentResolver = $this->getMockBuilder(ArgumentResolverInterface::class)->getMock();
        $argumentResolver
            ->expects($this->any())
            ->method('getArguments')
            ->will($this->returnValue($arguments));

        return new HttpKernel($eventDispatcher, $controllerResolver, $requestStack, $argumentResolver);
    }

}
