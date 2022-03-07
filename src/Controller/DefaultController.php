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

use App\Utils\Utils;

class DefaultController extends AbstractController
{

    /**
     * @throws Exception
     */

    public function homepage(): Response
    {
        return $this->render('default/index.html.twig');
    }

    public function product(int $id, ManagerRegistry $doctrine, Utils $utils)
    {
        // getting all the necessary repositories
        $product_repository = $doctrine->getRepository(Product::class);
        $comment_repository = $doctrine->getRepository(Comment::class);

        $product = $product_repository->find($id);
        if($product === null)
            die('This product was not found!');

        $comments = $comment_repository->findBy(['product' => $product]);

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

        return $this->render('default/product.html.twig', [
            'product' => $product,
            'comments' => $comments,
            'product_categories' => $product_categories,
            'product_images' => array($product->getImage()), //~_~ temporarily

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

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $product->getName(),
        ]);
    }

    public function category(int $id, ManagerRegistry $doctrine, Utils $utils)
    {
        $category_repository = $doctrine->getRepository(Category::class);

        $current_category = $category_repository->find($id);
        if($current_category === null)
            die("Category with id = $id was not found!");

        $parents_of_current_category = $category_repository->getAllParents($current_category);

        $breadcrumbs = $utils->convertCategoriesIntoBreadcrumbs($parents_of_current_category);

        return $this->render('default/category.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $current_category->getName(),
        ]);
    }
}