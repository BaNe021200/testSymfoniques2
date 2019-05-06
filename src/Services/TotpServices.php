<?php


namespace App\Services;


use App\EventSubscriber\TotpSubscriber;
use App\Form\TotpType;
use Doctrine\Common\Persistence\ObjectManager;
use Otp\GoogleAuthenticator;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;


use phpDocumentor\Reflection\Types\This;
use function Sodium\add;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * @method getUser()
 */
class TotpServices
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
     * @var CookiesBundle
     */
    private $cookiesBundle;

    /**
     * @var Session
     */
    private $sessionFlash;


    /**
     * TotpServices constructor.
     * @param UrlGeneratorInterface $router
     * @param FormFactoryInterface $formFactory
     * @param ObjectManager $em
     * @param Environment $renderer
     * @param Security $security
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     * @param CookiesBundle $cookiesBundle
     */
    public function __construct(UrlGeneratorInterface $router, FormFactoryInterface $formFactory, ObjectManager $em, Environment $renderer, Security $security, RequestStack $requestStack, SessionInterface $session, CookiesBundle $cookiesBundle)
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
                        'action' => $this->router->generate('user.totp.up')
                    ]
            ]
        );


        $this->renderer = $renderer;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->cookiesBundle = $cookiesBundle;

        $sessionFlash = new Session();
    }

    public function getForm()
    {
        return $this->form;
    }

    public function userTotp()
    {


        $sessionFlash = new Session();

        $request = $this->requestStack->getCurrentRequest();
        $form = $this->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $otp = new Otp();
            $code = $form['code']->getData();

            if ($otp->checkTotp(Encoding::base32DecodeUpper($this->session->get('secret')), $code)) {

                $user = $this->security->getUser();
                $user->setTotpKey($this->session->get('secret'));
                $user->addRole('ROLE_USER_CONNECTED');

                $this->em->persist($user);
                $this->em->flush();

                $this->session->remove('secret');

                /*if ($totpNoMore) {
                    $this->cookiesBundle->createCookie('userKey', 'userKey');
                    $sessionFlash->getFlashBag()->add('info','Vous avez coché l\'option ne plus demander d\'identifiaction à deux facteurs. Si vous souhaiter la retablir, supprimer le cookie \'userKey\' de votre navigateur');
                }
                else
                {
                    $getcookie = $this->cookiesBundle->getCookie('userKey');
                    if($getcookie)
                    {
                        $this->cookiesBundle->destroyCookie('userKey');
                    }

                }*/
                $totpNoMore = $form['totpNoMore']->getData();
                $this->totpNoMore($totpNoMore);


               $sessionFlash->getFlashBag()->add('success', 'validation à deux étapes activée');
                return 'totpOk';

            } else {
                return 'totpFailed';
            }
        }


    }


    public function totpNoMore($totpNoMore)
    {


        $sessionFlash = new Session();

        if ($totpNoMore) {

            $cookie = $this->cookiesBundle->createCookie('userKey', 'userKey');
            $sessionFlash->getFlashBag()->add('info', 'Vous avez coché l\'option ne plus demander d\'identifiaction à deux facteurs. Si vous souhaiter la retablir, supprimer le cookie \'userKey\' de votre navigateur');
            return $cookie;

        } else {


            $getcookie = $this->cookiesBundle->getCookie('userKey');
            if ($getcookie) {

                $this->cookiesBundle->destroyCookie('userKey');
            }

        }
    }


}