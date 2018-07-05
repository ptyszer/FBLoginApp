<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, SessionInterface $session)
    {
        $user = null;
        if($session->has('user')){
            $user = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['id' => $session->get('user')]);
        }

        $posts = $this->getDoctrine()->getRepository(Post::class)->findAll();
        return $this->render('main/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
