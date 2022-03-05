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

    public function getAllCategories(Product $product)
    {
        $categories = array();
        $category = $product->getCategory();
        do {
            array_push($categories, $category);
            $category = $category->getParent();
        } while ($category !== null);

        return $categories;
    }

    public function product($id, ManagerRegistry $doctrine)
    {
        // getting all necessary repositories
        $product_repository = $doctrine->getRepository(Product::class);
        $comment_repository = $doctrine->getRepository(Comment::class);

        $product = $product_repository->find($id);
        if($product === null)
            die('This product was not found!');

        $comments = $comment_repository->findBy(['product' => $product]);

        $all_categories_of_product = $this->getAllCategories($product);

        return $this->render('default/product.html.twig', [
            'product' => $product,
            'comments' => $comments,
            'product_categories' => $all_categories_of_product,
            'product_images' => array($product->getImage()), //~_~
        ]);
    }

    public function category($id)
    {
        die("Here will be a category page soon of id = $id");
    }
}