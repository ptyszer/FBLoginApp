<?php

namespace App\Controller;

use App\Service\FacebookApi;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/user")
 */
class UserController extends Controller
{

    /**
     * @Route("/login", name="user_login", methods="GET|POST")
     */
    public function login(SessionInterface $session): Response
    {
        if($session->has('user')){
            return $this->redirectToRoute('index');
        }

        $fb = new FacebookApi('846197532246789', '6f2abc63d407135200d93b14da40fe8d');
        $fb->setCallback($this->generateUrl('fb_callback', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $fb->setPermissions([
            'email',
            'public_profile',
            'user_location',
            'user_birthday',
            'user_friends',
            'user_gender',
        ]);

        return $this->redirect($fb->getLoginUrl());
    }

    /**
     * @Route("/callback", name="fb_callback", methods="GET|POST")
     */
    public function fbCallback(Request $request, SessionInterface $session): Response
    {
        $em = $this->getDoctrine()->getManager();
        $userService = new UserService($em);
        $fb = new FacebookApi('846197532246789', '6f2abc63d407135200d93b14da40fe8d');
        $fb->login($request);
        $userData = $fb->getUserData();
        $user = $userService->saveToDB($userData);
        $session->set('user', $user->getId());

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/logout", name="user_logout", methods="GET|POST")
     */
    public function logout(SessionInterface $session): Response
    {
        $session->remove('user');
        return $this->redirectToRoute('index');
    }

}
