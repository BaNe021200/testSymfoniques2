<?php

namespace App\Security;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TotpAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $encoder;
    private $router;

    public function __construct(EntityManagerInterface $em,
                                UserPasswordEncoderInterface $encoder,
                                UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->router = $router;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.

     */
    public function supports(Request $request)
    {
        // Toc, toc, quelqu'un essaie de se connecter avec totp...
        return $request->get('_route') == 'security.entry' && $request->isMethod('POST');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $credentials = $request->get('entry_form');
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];

        if (null === $username) {
            return;
        }

        // TODO: comment utiliser les user_provider ?
        return $this->em->getRepository(Member::class)
            ->findOneBy(['username' => $username]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['password'];
        return $this->encoder->isPasswordValid($user, $password);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // la destination par défaut
        $dest = $this->router->generate('member.connected.success');

        if ($token->getUser()->getTotpKey())
        {
            $token->setAuthenticated(false);
            // la destination si le totp est activé
            $dest = $this->router->generate('user.login.totp');
        }

        return new RedirectResponse($dest);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->redirectToLoginUrl();
    }

    /**
     * Called when authentication is needed
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->redirectToLoginUrl();
    }

    public function supportsRememberMe()
    {
        return false;
    }

    /**
    * Revenir au formulaire de connexion
    */
    private function redirectToLoginUrl()
    {
        $login = $this->router->generate('security.entry');
        return new RedirectResponse($login);
    }
}
