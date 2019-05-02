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
     * TotpServices constructor.
     * @param UrlGeneratorInterface $router
     * @param FormFactoryInterface $formFactory
     * @param ObjectManager $em
     * @param Environment $renderer
     * @param Security $security
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     *
     */
    public function __construct(UrlGeneratorInterface $router, FormFactoryInterface $formFactory, ObjectManager $em, Environment $renderer, Security $security, RequestStack $requestStack, SessionInterface $session)
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

            $data = $form->getData();

            $datum = [];
            foreach ($data as $k => $v) {
                $datum[$k] = $v;
            }

            if ($otp->checkTotp(Encoding::base32DecodeUpper($this->session->get('secret')), $datum['code'])) {

                $user = $this->security->getUser();
                $user->setTotpKey($this->session->get('secret'));
                $user->addRole('ROLE_USER_CONNECTED');

                $this->em->persist($user);
                $this->em->flush();

                $this->session->remove('secret');


                $sessionFlash->getFlashBag()->add('success', 'validation à deux étapes activée');
                return 'totpOk';

            } else {
                return 'totpFailed';
            }
        }


    }


    /* public function userTotpLogin()
     {
         $session = new Session();
         $request= $this->requestStack->getCurrentRequest();

         $form = $this->getForm();
         $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {

             # on récupére la clé du champ totpkey.

             $user = $this->security->getUser()->getTotpKey();
             $userTotpToken = uniqid('', true);
             $otp = new Otp();
             # on récupuère les données du formulaire : le code renvoyé par l'utlisateur et sa décision de créer ou non un cookie nommé userKey qui permet de ne plus avoir à rentrer de code.
             $data = $form->getData();

             $datum=[];
             foreach ($data as $k => $v)
             {
                 $datum[$k] = $v;
             }

             # on vérifie la clé du champs et le code retourné coincident.
             if ($otp->checkTotp(Encoding::base32DecodeUpper($user), $datum['code'])) {
                 $this->session->start();
                 #si l'utilisateur à coché alors un cookie est généré.
                 if($datum['totpNoMore']==true)
                 {
                     setCookie('userKey', $userTotpToken, time() + 3600 * 24 * 365, '', '', false, true);
                 }
                 else{
                     if(isset($_COOKIE['userKey']))
                     {
                         setcookie("userKey","", time()- 60);
                     }

                 }
                 # si le binôme clé/code est valide alors l'utilisateur est connecté.


                 $session->getFlashBag()->add('success', 'Welcome back ' . $this->getUser()->getUsername() . ' ! :-)');


                 return 'totpLoginMatch';
             } else {
                 $session->getFlashBag()->add('danger', 'Code incorrect');
                 return 'totpLoginFailed';
             }


         }

         return $this->renderer->render('login/loginTotp.html.twig', [
             'form' => $form->createView()
         ]);
     }*/


}