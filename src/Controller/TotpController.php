<?php


namespace App\Controller;


use App\Services\CookiesBundle;
use App\Services\TotpServices;
use Doctrine\Common\Persistence\ObjectManager;
use Otp\GoogleAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class TotpController extends AbstractController
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * TotpController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/totp",name="user.totp.up")
     * @param TotpServices $totpServices
     * @return Response
     */
    public function totp(TotpServices $totpServices)
    {
        //return new Response($totpServices->userTotp());

        $totp = $totpServices->userTotp();
        $form = $totpServices->getForm();

        if ($totp == 'totpOk') {

            return $this->redirectToRoute('member.connected.success');
        }
        if ($totp == 'totpFailed') {

            $this->addFlash('danger', 'Code incorrect');

        }


        /*return  new Response($totpServices->userTotp());*/


        $username = $this->getUser()->getUsername();
        $secret = GoogleAuthenticator::generateRandom();
        $qrcode = GoogleAuthenticator::getQrCodeUrl('totp', "tests Symfoniques : $username", $secret);
        $this->session->set('secret', $secret);
        return $this->render('login/totp.html.twig', [
            'qrcode' => $qrcode,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/totp/down",name="user.totp.down")
     * @param ObjectManager $manager
     * @param CookiesBundle $cookiesBundle
     * @return RedirectResponse
     */
    public function totpDown(ObjectManager $manager,CookiesBundle $cookiesBundle)
    {
        $this->session->start();
        $user = $this->getUser();
        $user->setTotpKey(null);


        $manager->persist($user);
        $manager->flush();

        $getCookie = $cookiesBundle->getCookie('userKey');
        if($getCookie)
        {
            $cookiesBundle->destroyCookie('userKey');
        }





        $this->addFlash('info', 'La validation à deux étapes est désactivée. Pensez à supprimer votre compte de votre application mobile');


        return $this->redirectToRoute('member.connected.success');
    }
}