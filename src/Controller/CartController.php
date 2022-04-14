<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use App\Form\CartType;
use App\Manager\CartManager;
use App\Entity\Order;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(CartManager $cart_manager, Request $request): Response
    {
        $cart = $cart_manager->getCurrentCart();
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $cart->setUpdatedAt(new \DateTime());
            $cart_manager->save($cart);

            return $this->redirectToRoute('cart');
        }

        return $this->render('order/cart.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    public function cartPreview(CartManager $cart_manager, ManagerRegistry $doctrine)
    {
        $cart = $cart_manager->getCurrentCart();

        $order_repository = $doctrine->getRepository(Order::class);

        $items = $order_repository->getFirstItems($cart);
        $total_count_of_items = $cart->getTotalQuantity();

        return $this->render('order/_cart_preview.html.twig', [
            'items' => $items,
            'total_count_of_items' => $total_count_of_items,
            'total_price' => $cart->getTotal(),
        ]);
    }
}
