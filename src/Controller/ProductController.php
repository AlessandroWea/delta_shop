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

class ProductController extends AbstractController
{

    public function product(int $id, ManagerRegistry $doctrine, Utils $utils)
    {
        // getting all the necessary repositories
        $product_repository = $doctrine->getRepository(Product::class);
        $comment_repository = $doctrine->getRepository(Comment::class);
        $category_repository = $doctrine->getRepository(Category::class);

        $product = $product_repository->find($id);
        if($product === null)
            throw $this->createNotFoundException('The product does not exist');

        $comments = $product->getComments();

        $top_categories = $category_repository->findBy(['Parent' => null]);

        //making breadcrumbs
        $product_categories = $product_repository->getAllCategories($product);
        $breadcrumbs = $utils->convertCategoriesIntoBreadcrumbs($product_categories);

        //rating calculations
        $count_of_comments_with_one_star = $product_repository->getCommentsCountByRating($product, 1);
        $count_of_comments_with_two_star = $product_repository->getCommentsCountByRating($product, 2);
        $count_of_comments_with_three_star = $product_repository->getCommentsCountByRating($product, 3);
        $count_of_comments_with_four_star = $product_repository->getCommentsCountByRating($product, 4);
        $count_of_comments_with_five_star = $product_repository->getCommentsCountByRating($product, 5);

        $sum_of_ratings = $count_of_comments_with_one_star +
                          $count_of_comments_with_two_star +
                          $count_of_comments_with_three_star +
                          $count_of_comments_with_four_star +
                          $count_of_comments_with_five_star;

        if($sum_of_ratings === 0)
        {
            $average_rating = 0;
        }
        else
        {
            $average_rating = ($count_of_comments_with_one_star * 1 +
                              $count_of_comments_with_two_star * 2 +
                              $count_of_comments_with_three_star* 3 +
                              $count_of_comments_with_four_star * 4 +
                              $count_of_comments_with_five_star * 5) / $sum_of_ratings;
        }

        $floored_rating = floor($average_rating);


        return $this->render('product/product.html.twig', [
            'product' => $product,
            'comments' => $comments,
            'product_categories' => $product_categories,

            'count_of_comments_with_one_star' => $count_of_comments_with_one_star,
            'count_of_comments_with_two_star' => $count_of_comments_with_two_star,
            'count_of_comments_with_three_star' => $count_of_comments_with_three_star,
            'count_of_comments_with_four_star' => $count_of_comments_with_four_star,
            'count_of_comments_with_five_star' => $count_of_comments_with_five_star,

            'rating_one_star_in_percents' => $utils->toPercent($count_of_comments_with_one_star, $sum_of_ratings),
            'rating_two_star_in_percents' => $utils->toPercent($count_of_comments_with_two_star, $sum_of_ratings),
            'rating_three_star_in_percents' => $utils->toPercent($count_of_comments_with_three_star, $sum_of_ratings),
            'rating_four_star_in_percents' => $utils->toPercent($count_of_comments_with_four_star, $sum_of_ratings),
            'rating_five_star_in_percents' => $utils->toPercent($count_of_comments_with_five_star, $sum_of_ratings),

            'average_rating' => $average_rating,
            'floored_rating' => $floored_rating,

            'top_categories' => $top_categories,

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $product->getName(),
        ]);
    }

    public function productCart(Product $product, ManagerRegistry $doctrine)
    {
        return $this->render('product/_product_cart.html.twig', [
            'product' => $product
        ]);  
    }

    public function productRatingShort(Product $product, ManagerRegistry $doctrine)
    {
        $product_repository = $doctrine->getRepository(Product::class);


        $count = count($product->getComments());
        if($count === 0)
            $average_rating = 0;
        else
            $average_rating = ($product_repository->getCommentsCountByRating($product, 1) * 1 +
                               $product_repository->getCommentsCountByRating($product, 2) * 2 +
                               $product_repository->getCommentsCountByRating($product, 3) * 3+
                               $product_repository->getCommentsCountByRating($product, 4) * 4+
                               $product_repository->getCommentsCountByRating($product, 5) * 5)/ $count;


        return $this->render('default/_rating_short.html.twig', [
            'rating' => floor($average_rating)
        ]);  
    }


}
