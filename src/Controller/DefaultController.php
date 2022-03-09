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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\Category;

use App\Utils\Utils;

class DefaultController extends AbstractController
{

    /**
     * @throws Exception
     */

    public function homepage(ManagerRegistry $doctrine): Response
    {
        $category_repository = $doctrine->getRepository(Category::class);
        $product_repository = $doctrine->getRepository(Product::class);

        $top_categories = $category_repository->findBy(['Parent' => null]);

        return $this->render('default/index.html.twig', [
            'categories' => $top_categories,
        ]);
    }

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

            'top_categories' => $top_categories,

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $product->getName(),
        ]);
    }

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

        return $this->render('default/category.html.twig', [
            'products' => $products,
            'product_ratings' => $product_ratings,

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $current_breadcrumb,
        ]);
    }

    public function searchBar(ManagerRegistry $doctrine)
    {
        $category_repository = $doctrine->getRepository(Category::class);

        $categories = $category_repository->findBy(['Parent' => null]);

        return $this->render('default/_search_bar.html.twig', [
            'categories' => $categories,
        ]);    
    }

    public function footer(ManagerRegistry $doctrine)
    {
        $category_repository = $doctrine->getRepository(Category::class);

        $categories = $category_repository->findBy(['Parent' => null]);

        return $this->render('default/_footer.html.twig', [
            'categories' => $categories,
        ]);    
    }

    public function topNavigation(ManagerRegistry $doctrine)
    {
        $category_repository = $doctrine->getRepository(Category::class);

        $categories = $category_repository->findBy(['Parent' => null]);

        return $this->render('default/_top_nav.html.twig', [
            'categories' => $categories,
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

    public function commentRatingShort(Comment $comment, ManagerRegistry $doctrine)
    {
        $rating = $comment->getRating();

        return $this->render('default/_rating_short.html.twig', [
            'rating' => $rating,
        ]);  
    }

    public function productCart(Product $product, ManagerRegistry $doctrine)
    {
        return $this->render('default/_product_cart.html.twig', [
            'product' => $product
        ]);  
    }

    public function newProductsList(Category $category, ManagerRegistry $doctrine)
    {
        $category_repository = $doctrine->getRepository(Category::class);
        $last_sub_categories = $category_repository->getAllLastSubCategoriesId($category);

        $categories = [];

        foreach($last_sub_categories as $cat)
        {
            $categories[] = $category_repository->find($cat);
        }

        $product_repository = $doctrine->getRepository(Product::class);
        $products = $product_repository->getRecentProductsByCategories($categories);

        return $this->render('default/_new_products_list.html.twig', [
            'products' => $products
        ]);  
    }

    public function newProductsSection(ManagerRegistry $doctrine)
    {

        $categories = $doctrine->getRepository(Category::class)->findBy(['Parent'=>null]);

        return $this->render('default/_new_products_section.html.twig', [
            'categories' => $categories,
        ]);  
    }

    public function randomProductsSection(ManagerRegistry $doctrine)
    {

        $categories = $doctrine->getRepository(Category::class)->findBy(['Parent'=>null]);

        return $this->render('default/_random_products_section.html.twig', [
            'categories' => $categories,
        ]);  
    }

    public function categoryListTabSwitchers(ManagerRegistry $doctrine)
    {
        $categories = $doctrine->getRepository(Category::class)->findBy(['Parent'=>null]);

        return $this->render('default/_category_list_tab_switchers.html.twig', [
            'categories' => $categories,
        ]); 
    }
    
}