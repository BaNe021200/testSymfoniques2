<?php


namespace App\Controller;


use App\Form\LoginType;
use App\Form\EntryType;
use App\Services\CookiesBundle;
use App\Services\TotpLogin;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SecurityController
 * @Route("/security")
 */
class SecurityController extends AbstractController
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * SecurityController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    /**
     * @Route("/login", name="security.login")
     * @param AuthenticationUtils $authenticationUtils
     * @param ObjectManager $manager
     * @param Request $request
     * @param CookiesBundle $cookiesBundle
     * @return RedirectResponse
     */
    public function login(AuthenticationUtils $authenticationUtils, ObjectManager $manager,Request $request,CookiesBundle $cookiesBundle)
    {
        if ($this->getUser())
        {
            $getCookie = $cookiesBundle->getCookie('userKey');
            # 1 on vérifie si un cookies est présent. si oui on connecte directement l'utilisateur
            if ($getCookie) {


                $this->addFlash('success', 'Welcome back ' . $this->getUser()->getUsername() . ' ! :-)');
                return $this->redirectToRoute('member.connected.success');
            }
                # s'il n'y pas de cookie on vérifie si l'utilisateur à activé la validation TOTP.
                # validation à deux facteurs : si le champ TotpKey de la BDD est rempli la TOTP est activée.
                #si la TOTP est activée on rentre l'utilisateur en session et on le redirige vers la route qui gère le totp
                #si la TOTP est désactivée (le champs totpkey est vide) on connecte l'utilisateur.
                if ($this->getUser()->getTotpKey() != null) {
                    $this->session->start();
                    $this->session->set('userId', $this->getUser()->getId());
                    return $this->redirectToRoute('user.login.totp');
                } else {

                    $this->addFlash('success', 'Welcome back ' . $this->getUser()->getUsername() . ' ! :-)');
                    return $this->redirectToRoute('member.connected.success');

                }

        }


        $form = $this->createForm(LoginType::class, [
            'username' => $authenticationUtils->getLastUsername()
        ]);


        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('login/login.html.twig', [

            'form' => $form->createView(),
            'last_username' => $lastUsername,

            'error' => $error

        ]);
    }

    /**
     * @param Request $request
     * @param TotpLogin $totpLogin
     * @return RedirectResponse|Response
     * @Route("/user/login/totp",name="user.login.totp")
     */
    public function loginTotp(Request $request, TotpLogin $totpLogin)
    {


        # Si l'utilisateur n'est pas en session on le redirige vers la page de login.

        if (!$this->session->get('userId')) {
            return $this->redirectToRoute('security.login');

        }
        $totp = $totpLogin->userTotpLogin();
        $form = $totpLogin->getForm();
        if ($totp == 'totpLoginMatch') {

            return $this->redirectToRoute('member.connected.success');
        } elseif ($totp == 'totpLoginFailed') {
            $this->addFlash('danger', 'Code incorrect');
        }

        return $this->render('login/loginTotp.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/validate", name="security.validate")
     */
    public function validation()
    {
        // Aucune des actions de cette route n'est exécuté.
        // Mais elle est néanmoins récupérer par le guardhandler
        die('ok');
        dump('En passant par validate ?');
    }

    /**
     * @Route("/logout", name="security.logout")
     */
    public function logout ()
    {

    }

    /**
     * @Route("/entry", name="security.entry", methods={"GET", "POST"})
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param ObjectManager $manager
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function entry(AuthenticationUtils $authenticationUtils,
                          TokenStorageInterface $token)
    {
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(EntryType::class, [
            'username' => $lastUsername
        ]);

        $user = $token->getToken()->getUser();
        if ($user)
        {
            /* TODO: initialiser le formulaire totp
            $form = $this->createForm(TotpType::class, [
                'username' => $lastUsername
            ]);
            */
            dump($user);
        }
        return $this->render('login/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }
}
