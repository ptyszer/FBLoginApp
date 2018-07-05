<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/post")
 */
class PostController extends Controller
{
    /**
     * @Route("/new", name="post_new", methods="GET|POST")
     */
    public function new(Request $request, SessionInterface $session): Response
    {
        if(!$session->has('user')){
            return $this->redirectToRoute('user_login');
        }

        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['id' => $session->get('user')]);

        $post = new Post();
        $post->setUser($user);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="post_edit", methods="GET|POST")
     */
    public function edit(Request $request, Post $post, SessionInterface $session): Response
    {
        if(!$session->has('user')){
            return $this->redirectToRoute('user_login');
        }

        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['id' => $session->get('user')]);

        if($post->getUser() != $user){
            return $this->redirectToRoute('index');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_edit', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods="DELETE")
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('index');
    }
}
