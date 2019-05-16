<?php


namespace App\Services;


use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CookiesBundle
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * CookiesActions constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function createCookie($name,$value)
    {
        $response = new Response();


        $response->headers->setCookie(new Cookie($name, $value, time() + 3600 * 24 * 365, '', '', false, true));
        $response->send();

    }

    public function getCookie($name)
    {
        $cookie = $this->requestStack->getCurrentRequest()->cookies->get($name);
        return $cookie;
    }


    public function destroyCookie($name)
    {
        $response = new Response();
        $response->headers->clearCookie($name);
        $response->send();
    }
}