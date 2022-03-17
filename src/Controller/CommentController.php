<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Comment;

use App\Form\CommentFormType;

use App\Entity\Product;

class CommentController extends AbstractController
{

    /**
     * @Route("/comment/{id}", name="comment")
     */
    public function commentForm(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment, [
            'action' => $this->generateUrl('comment', ['id' => $id])
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('comment/comment_form.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }
}
