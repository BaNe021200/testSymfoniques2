<?php


namespace App\Services;


use App\Form\TotpType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Cookie;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Twig\Environment;

class TotpLogin
{
    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var FormFactory
     */
    private $factory;
    /**
     * @var UrlGeneratorInterface
     */
    private $router;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    private $form;
    /**
     * @var Environment
     */
    private $renderer;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var TokenStorage
     */
    private $tokenStorage;
    /**
     * @var TotpServices
     */
    private $totpServices;
    /**
     * @var CookiesBundle
     */
    private $cookiesBundle;


    /**
     * TotpServices constructor.
     * @param UrlGeneratorInterface $router
     * @param FormFactoryInterface $formFactory
     * @param ObjectManager $em
     * @param Environment $renderer
     * @param Security $security
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param TotpServices $totpServices
     * @param CookiesBundle $cookiesBundle
     */
    public function __construct(UrlGeneratorInterface $router, FormFactoryInterface $formFactory, ObjectManager $em, Environment $renderer, Security $security, RequestStack $requestStack, SessionInterface $session, TokenStorageInterface $tokenStorage, TotpServices $totpServices, CookiesBundle $cookiesBundle)
    {

        $this->em = $em;
        $this->router = $router;
        $this->formFactory = $formFactory;

        $this->form = $this->formFactory->create(

            TotpType::class,
            NULL,
            [
                'attr' =>
                    [
                        'action' => $this->router->generate('user.login.totp')
                    ]
            ]
        );


        $this->renderer = $renderer;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;


        $this->totpServices = $totpServices;
        $this->cookiesBundle = $cookiesBundle;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function userTotpLogin()
    {
        $session = new Session();
        $request = $this->requestStack->getCurrentRequest();

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            # on récupére la clé du champ totpkey.

            $user = $this->security->getUser()->getTotpKey();
            $userTotpToken = uniqid('', true);
            $otp = new Otp();
            # on récupuère les données du formulaire : le code renvoyé par l'utlisateur et sa décision de créer ou non un cookie nommé userKey qui permet de ne plus avoir à rentrer de code.
            $data = $form->getData();
            $code = $form['code']->getData();
            $totpNoMore = $form['totpNoMore']->getData();
            //$getCookie = $this->cookiesBundle->getCookie('userKey');


            # on vérifie la clé du champs et le code retourné coincident.
            if ($otp->checkTotp(Encoding::base32DecodeUpper($user), $code)) {

                #si l'utilisateur à coché alors un cookie est généré.

                $this->totpServices->totpNoMore($totpNoMore);


                # si le binôme clé/code est valide alors l'utilisateur est connecté.


                $session->getFlashBag()->add('success', 'Welcome back ' . $this->security->getUser()->getUsername() . ' ! :-)');

                $addRole = $this->security->getUser()->addRole('ROLE_USER_CONNECTED');
                $this->em->persist($this->security->getUser());
                $this->em->flush();

                /*$token = new UsernamePasswordToken($this->security->getUser(),null,'main',$this->security->getUser()->getRoles());

                //$this->container->get('security.context')->setToken($token);
                $this->tokenStorage->setToken($token);*/


                return 'totpLoginMatch';
            } else {

                return 'totpLoginFailed';
            }


        }


    }


}