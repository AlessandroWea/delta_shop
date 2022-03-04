<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\Category;

class DefaultController extends AbstractController
{

    /**
     * @throws Exception
     */

    public function homepage(): Response
    {
        return $this->render('default/index.html.twig');
    }

    public function product($id, ManagerRegistry $doctrine)
    {
        $product_repository = $doctrine->getRepository(Product::class);
        $comment_repository = $doctrine->getRepository(Comment::class);
        $category_repository = $doctrine->getRepository(Category::class);

        $product = $product_repository->find($id);
        if($product === null)
            die;



        return $this->render('default/product.html.twig', [
            'product' => $product,
        ]);
    }
}