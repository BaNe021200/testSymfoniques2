<?php


namespace App\Services;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccess implements LogoutSuccessHandlerInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * LogoutSuccess constructor.
     * @param ObjectManager $manager
     * @param Security $security
     * @param SessionInterface $session
     * @param UrlGeneratorInterface $router
     */
    public function __construct(ObjectManager $manager, Security $security,SessionInterface $session, UrlGeneratorInterface $router )
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->session = $session;
        $this->router = $router;
    }


    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {



        $this->security->getUser()->removeRole('ROLE_USER_CONNECTED');
        $this->manager->persist($this->security->getUser());
        $this->manager->flush();
        $this->session->getFlashBag()->add('success', 'Vous êtes maintenant déconnecté');
        return new RedirectResponse($this->router->generate('index'));
    }
}