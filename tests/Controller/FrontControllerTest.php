<?php

namespace App\tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Common\Persistence\ObjectManager;

use App\Kernel;
use App\Controller\FrontController;
use App\Controller\SecurityController;

class FrontControllerTest extends TestCase
{

    private $container;
    private $kernel;

    public function setUp()
    {
        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
    }

    public function testIndex()
    {
        $request = $this->mockRequest('/');
        $response = $this->kernel->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testLogin()
    {

/*
    Tester son application depuis le kernel de test.
    1) Soit en récupérant les services du container de services:

        $authUtils = $this->container->get('security.authentication_utils');
        $storage = $this->container->get('security.token_storage');
        $objManager = $this->container->get('doctrine.orm.default_entity_manager');

    2) Soit en créant des mock (a préciser)
        $this->getMockBuilder(AuthenticationUtils::class)
          ->disableOriginalConstructor()
          ->getMock();

        $manager = $this->getMockBuilder(ObjectManager::class)
          ->disableOriginalConstructor()
          ->getMock();

        $request = $this->getMockBuilder(Request::class)
          ->disableOriginalConstructor()
          ->getMock();

    3) Ensuite on crée la requête
        $controller = new SecurityController(new Session());
        $this->assertSame(200, $controller->entry($authUtils, $objManager)->getStatusCode());

    OU BIEN on transmet une requête au kernel de test, il se charge de tout le reste.

*/
        $request = $this->mockRequest('/security/login');
        $response = $this->kernel->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $request = $this->mockRequest('/security/entry');
        $response = $this->kernel->handle($request);
        $this->assertSame(200, $response->getStatusCode());

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

    private function mockRequest($requestUri, $files = array(), $request = array())
    {
        $query = array();
        $request = $request;
        $attributes = array();
        $cookies = array();
        $files = $files;
        $server = [
          'HTTP_HOST' => 'symfony',
          'HTTP_USER_AGENT' => 'Symfony Test Mocker',
          'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
          'HTTP_ACCEPT_LANGUAGE' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
          'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
          'HTTP_COOKIE' => 'PHPSESSID=md26ih06r38se8hgtteohcq7t0',
          'HTTP_DNT' => '1',
          'HTTP_CONNECTION' => 'keep-alive',
          'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
          'HTTP_CACHE_CONTROL' => 'max-age=0',
          'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
          'SERVER_SIGNATURE' => 'Fake Server',
          'SERVER_SOFTWARE' => 'Apache/2.4.38 (Debian)',
          'SERVER_NAME' => 'symfony',
          'SERVER_ADDR' => '10.1.2.6',
          'SERVER_PORT' => '80',
          'REMOTE_ADDR' => '169.254.153.109',
          'DOCUMENT_ROOT' => '/srv/ben/public',
          'REQUEST_SCHEME' => 'http',

          'CONTEXT_PREFIX' => '',
          'CONTEXT_DOCUMENT_ROOT' => '/srv/ben/public',
          'SERVER_ADMIN' => 'webmaster@localhost',
          'SCRIPT_FILENAME' => '/srv/ben/public/index.php',
          'REMOTE_PORT' => '49336',
          'GATEWAY_INTERFACE' => 'CGI/1.1',
          'SERVER_PROTOCOL' => 'HTTP/1.1',
          'REQUEST_METHOD' => 'POST',
          'QUERY_STRING' => '',
          'REQUEST_URI' => $requestUri,
          'SCRIPT_NAME' => '/index.php',
          'PHP_SELF' => '/index.php',
          'REQUEST_TIME_FLOAT' => '1553117305.946',
          'REQUEST_TIME' => '1553117305',
          'APP_ENV' => 'dev',
          'APP_SECRET' => 'ccfbecee00705d226aa0f7c5aa9fdfb0',
          'MAILER_URL' => 'null://localhost',
          'SYMFONY_DOTENV_VARS' => 'APP_ENV,APP_SECRET,MAILER_URL,DATABASE_URL',
          'DATABASE_URL' => 'sqlite:///%kernel.project_dir%/var/data.db',
          'APP_DEBUG' => '1',
          'SHELL_VERBOSITY' => '3',
        ];
        $content = null;

        return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
    }
}
