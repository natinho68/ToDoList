<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Method("GET")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $response = $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
        $response->setSharedMaxAge(15);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        return $response;
    }

    /**
     * @Route("/login_check", name="login_check")
     * @Method("POST")
     */
    public function loginCheck()
    {
        // This code is never executed.
    }

    /**
     * @Route("/logout", name="logout")
     * @Method("GET")
     */
    public function logoutCheck()
    {
        // This code is never executed.
    }

    /**
     * @Route("/logout_exp", name="logout_exp")
     * @Method("GET")
     */
    public function logOutExpire(Response $response = NULL)
    {
        if ($response) {
            $response->expire();
        }

        return $this->redirectToRoute('homepage');
    }
}
