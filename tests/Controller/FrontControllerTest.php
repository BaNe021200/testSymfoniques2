<?php

namespace App\tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Common\Persistence\ObjectManager;

use App\Controller\FrontController;
use App\Controller\SecurityController;
use App\Kernel;

class FrontControllerTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $kernel_test = new Kernel('test', true);
        $kernel_test->boot();
        $this->container = $kernel_test->getContainer();
    }

    public function testIndex()
    {
        $controller = new FrontController();
        $controller->setContainer($this->container);
        $this->assertSame(200, $controller->index()->getStatusCode());
    }

    public function testLogin()
    {
        $session = new Session();
        $controller = new SecurityController($session);
        $controller->setContainer($this->container);

        $auth = $this->getMockBuilder(AuthenticationUtils::class)
          ->disableOriginalConstructor()
          ->getMock();

        $manager = $this->getMockBuilder(ObjectManager::class)
          ->disableOriginalConstructor()
          ->getMock();

        $request = $this->getMockBuilder(Request::class)
          ->disableOriginalConstructor()
          ->getMock();

        $this->assertSame(200, $controller->login($auth, $manager, $request)->getStatusCode());
        $this->assertSame('', $this->container->get('http_kernel'));
        $this->assertSame('', $this->container->get('security.token_storage'));

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
