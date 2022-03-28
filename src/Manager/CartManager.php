<?php

namespace App\Manager;

use App\Entity\Order;
use App\Factory\OrderFactory;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

use App\Repository\OrderRepository;

class CartManager
{
	private $cart_session_storage;
	private $cart_factory;
	private $entity_manager;
	private $security;
	private $cart_repository;

	public function __construct(CartSessionStorage $cart_storage, OrderFactory $cart_factory, OrderRepository $cart_repository, EntityManagerInterface $entity_manager, Security $security)
	{
		$this->cart_session_storage = $cart_storage;
		$this->cart_factory = $cart_factory;
		$this->entity_manager = $entity_manager;
		$this->security = $security;
		$this->cart_repository = $cart_repository;
	}

	public function getCurrentCart(): Order
	{
		$user = $this->security->getUser();
		if($user)
		{
			//try to find in database
			$cart = $this->cart_repository->findOneBy([
				'user' => $user,
				'status' => Order::STATUS_CART,
			]);

			if(!$cart) //if not found -> make a new one
			{
				$cart = $this->cart_factory->createWithUser($user);
				$this->save($cart);
			}
		}
		else
		{
			//get a cart from session
			$cart = $this->cart_session_storage->getCart();
			if(!$cart)
			{
				//create a new cart if it wasn't created
				$cart = $this->cart_factory->create();
				$this->save($cart);			
			}
		}

		return $cart;
	}

	public function save(Order $cart): void
	{
		//persist in database
		$this->entity_manager->persist($cart);
		// dd($cart);
		$this->entity_manager->flush();

		//persist in session
		$this->cart_session_storage->setCart($cart);
	}
}