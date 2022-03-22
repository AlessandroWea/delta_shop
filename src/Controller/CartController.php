<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Form\CommentFormType;
use App\Form\OrderFormType;
use App\Form\RegistrationFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;





class CartController extends AbstractController
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
     * @Route("cart/add/{id<\d+>}", name="cartAdd")
     *
     * @param Product $product
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function cartAdd(Product $product, EntityManagerInterface $em): Response
    {
        $sessionId = $this->session->getId();

        $cart = (new Cart())
            ->setProduct($product)
            ->setCount(1)
            ->setSessionId($sessionId);

        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute('product', ['id' => $product->getId()]);
    }

    /**
     * @Route("/cart", name="cart")
     * @param CartRepository $cartRepository
     * @return Response
     */
    public function cart(CartRepository $cartRepository): Response
    {
        $session = $this->session->getId();
        $items = $cartRepository->findBy(['sessionId' => $session]);

        return $this->render(
            'order/cart.html.twig',
            [
                'title' => 'Cart',
                'items' => $items,
            ]
        );
    }


    /**
     * @Route("/order", name="order")
     * @param CartRepository $cartRepository
     * @return Response
     */

    public function orderGetProduct(CartRepository $cartRepository): Response
    {
        $session = $this->session->getId();
        $items = $cartRepository->findBy(['sessionId' => $session]);
        $form = $this->createForm(OrderFormType::class);
        return $this->render(
            'order/order.html.twig',
            [
                'formOrder' => $form->createView(),
                'items' => $items,
            ]
        );

    }

    /**
     * @Route("/cart/clear", name="cartClear")
     */

    public function cartClear(): \Symfony\Component\HttpFoundation\RedirectResponse //Clear session
    {
        $this->session->migrate();
        return $this->redirectToRoute('cart');
    }



}
