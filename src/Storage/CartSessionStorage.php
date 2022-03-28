<?php

namespace App\Storage;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class CartSessionStorage
{
	private $session;

	private $cart_repository;

	private $security;

	const CART_KEY_NAME = 'cart_id';

	public function __construct(SessionInterface $session, OrderRepository $cart_repository, Security $security)
	{
		$this->session = $session;
		$this->cart_repository = $cart_repository;
		$this->security = $security;
	}

	public function getCart() : ?Order
	{
		if($this->getCartId() !== null)
		{
			return $this->cart_repository->findOneBy([
				'id' => $this->getCartId(),
				'status' => Order::STATUS_CART,
			]);
		}

		return null;
	}

	public function setCart(Order $cart)
	{
		$this->session->set(self::CART_KEY_NAME, $cart->getId());
	}

	private function getCartId(): ?int
	{
		return $this->session->get(self::CART_KEY_NAME);
	}

}