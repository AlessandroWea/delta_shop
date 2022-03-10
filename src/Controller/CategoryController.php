<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\Category;

use App\Utils\Utils;

class CategoryController extends AbstractController
{

    public function category(int $id, ManagerRegistry $doctrine, Utils $utils)
    {
        //getting all repositories
        $category_repository = $doctrine->getRepository(Category::class);
        $product_repository = $doctrine->getRepository(Product::class);

        //if category is not found -> display all products
        $current_category = $category_repository->find($id);
        if($current_category === null)
        {
            $products = $product_repository->findAll();

            $breadcrumbs = array();
            $current_breadcrumb = 'All categories';
        }
        else
        {
            $products = $current_category->getProducts();

            $parents_of_current_category = $category_repository->getAllParents($current_category);
            $breadcrumbs = $utils->convertCategoriesIntoBreadcrumbs($parents_of_current_category);

            $current_breadcrumb = $current_category->getName();
        }

        //calculating ratings of every product
        $product_ratings = array();
        foreach($products as $product)
        {
            $count = count($product->getComments());
            if($count === 0)
                $average_rating = 0;
            else
                $average_rating = ($product_repository->getCommentsCountByRating($product, 1) * 1 +
                                   $product_repository->getCommentsCountByRating($product, 2) * 2 +
                                   $product_repository->getCommentsCountByRating($product, 3) * 3+
                                   $product_repository->getCommentsCountByRating($product, 4) * 4+
                                   $product_repository->getCommentsCountByRating($product, 5) * 5)/ $count;

            array_push($product_ratings, floor($average_rating));
        }

        return $this->render('category/category.html.twig', [
            'products' => $products,
            'product_ratings' => $product_ratings,

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $current_breadcrumb,
        ]);
    }
}
