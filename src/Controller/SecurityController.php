<?php


namespace App\Controller;


use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @Route("/security")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security.login")
     * @param AuthenticationUtils $authenticationUtils
     * @return RedirectResponse
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if($this->getUser())
        {
            $this->addFlash('success', 'Bienvenue '.$this->getUser()->getUsername(). ' !');
            return $this->redirectToRoute('member.connected.success');
        }
        $form = $this->createForm(LoginType::class,[
            'username' => $authenticationUtils->getLastUsername()
        ]);


        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig',[
            'form' => $form->createView(),
            'lastUsername' =>$lastUsername,
            'error' => $error,

        ]);
    }

    /**
     * @Route("/validate", name="security.validate")
     */
    public function validation()
    {

    }

    /**
     * @Route("/logout", name="security.logout")
     */
    public function logout ()
    {

    }
}