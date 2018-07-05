<?php

namespace App\Controller;

use App\Entity\User;
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
    public function login(Request $request, SessionInterface $session): Response
    {
        $session->start();

        if($session->has('user')){
            return $this->redirectToRoute('index');
        }

        $fb = new \Facebook\Facebook([
            'app_id' => '846197532246789',
            'app_secret' => '6f2abc63d407135200d93b14da40fe8d',
            'default_graph_version' => 'v3.0',
            'persistent_data_handler' => 'session',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = [
            'email',
            'public_profile',
            'user_location',
            'user_birthday',
            'user_friends',
            'user_gender',
        ];
        $redirectUrl = $this->generateUrl('fb_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $loginUrl = $helper->getLoginUrl($redirectUrl, $permissions);


        return $this->redirect($loginUrl);
    }

    /**
     * @Route("/callback", name="fb_callback", methods="GET|POST")
     */
    public function fbCallback(Request $request, SessionInterface $session): Response
    {
        $session->start();

        $fb = new \Facebook\Facebook([
            'app_id' => '846197532246789',
            'app_secret' => '6f2abc63d407135200d93b14da40fe8d',
            'default_graph_version' => 'v3.0',
            'persistent_data_handler' => 'session',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        if ($request->query->get('state') != null) {
            $helper->getPersistentDataHandler()->set('state', $request->query->get('state'));
        }

        try {
            $accessToken = $helper->getAccessToken();// to fetch access token
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken))// checks whether access token is in there or not
        {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }
        try {
            $response = $fb->get('/me?fields=id,name,email,birthday,picture.type(large),first_name,last_name,friends,gender,location', $accessToken); // to get required fields using access token
        } catch (\Facebook\Exceptions\FacebookResponseException $e)// throws an error if invalid fields are specified
        {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $userData = $response->getGraphUser();// to get user details

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'fbId' => $userData['id']
        ]);

        if(!isset($user)){
            $user = new User();
        }

        $user->setFbId($userData['id']);
        $user->setEmail($userData['email']);
        $user->setFirstName($userData['first_name']);
        $user->setLastName($userData['last_name']);
        $user->setGender(isset($userData['gender']) ? $userData['gender'] : null);
        $user->setCity(isset($userData['location']) ? $userData['location']['name'] : null);
        $user->setPicture(isset($userData['picture']) ? $userData['picture']['url'] : null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $session->set('user', $user->getId());

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/logout", name="user_logout", methods="GET|POST")
     */
    public function logout(Request $request, SessionInterface $session): Response
    {
        $session->remove('user');
        return $this->redirectToRoute('index');
    }

}
