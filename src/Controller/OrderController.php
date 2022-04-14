<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 *
 * @IsGranted("ROLE_USER")
 */
class OrderController extends AbstractController
{
	public function index()
	{
		return $this->render('order/order.html.twig', [

		]);
	}
}