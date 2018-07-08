<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session, PostRepository $postRepository, UserRepository $userRepository)
    {
        $user = null;
        if($session->has('user')){
            $user = $userRepository->findOneBy(['id' => $session->get('user')]);
        }

        return $this->render('main/index.html.twig', [
            'user' => $user,
            'posts' => $postRepository->findAll(),
        ]);
    }

}
