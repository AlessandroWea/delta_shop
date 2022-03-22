<?php

namespace App\Controller;

use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartModController extends AbstractController
{
    private SessionInterface $session;

    /**
     * IndexController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->session->start();
    }

    /**
     * @param CartRepository $cartRepository
     * @return Response
     */
    public function cartMod(CartRepository $cartRepository): Response
    {
        $session = $this->session->getId();
        $items = $cartRepository->findBy(['sessionId' => $session]);

        return $this->render(
            'default/_cart.html.twig',
            [
                'items' => $items,
            ]
        );
    }

    /**
     * @Route("/cart/clear", name="cartModClear")
     */

    public function cartModClear(): \Symfony\Component\HttpFoundation\RedirectResponse //Clear session
    {

        $this->session->migrate($session);

        return $this->redirectToRoute('cart');
    }


}