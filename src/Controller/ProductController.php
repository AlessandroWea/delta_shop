<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\Category;

use App\Utils\Utils;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;

class ProductController extends AbstractController
{

    public function product(int $id, ProductRepository $product_repository, ManagerRegistry $doctrine, Utils $utils, Request $request,  CartManager $cart_manager)
    {
        $form = $this->createForm(AddToCartType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //add new item
            $item = $form->getData();
            $item->setProduct($product_repository->find($id));
            $item->setPrice($product_repository->find($id)->getPrice());
            // dd($item);
            $cart = $cart_manager->getCurrentCart();
            $cart->addItem($item);

            $cart_manager->save($cart);

            return $this->redirectToRoute('product', [
                'id' => $id,
            ]);
        }
        // getting all the necessary repositories
        // $product_repository = $doctrine->getRepository(Product::class);
        $comment_repository = $doctrine->getRepository(Comment::class);
        $category_repository = $doctrine->getRepository(Category::class);

        $product = $product_repository->find($id);
        if($product === null)
            throw $this->createNotFoundException('The product does not exist');

        $comments = $product->getComments();

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

        $average_rating = number_format($average_rating, 2);

        $floored_rating = floor($average_rating);

        $gallery = $product->getGallery()->getGalleryHasMedias();
        $additional_images = [];

        foreach($gallery as $g)
        {
            $additional_images[] = $g->getMedia();
        }

        return $this->render('product/product.html.twig', [
            'add_to_cart_form' => $form->createView(),

            'product' => $product,
            'comments' => $comments,
            'product_categories' => $product_categories,

            'additional_images' => $additional_images,

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

    public function productCart(Product $product, ManagerRegistry $doctrine)
    {
        return $this->render('product/_product_cart.html.twig', [
            'product' => $product
        ]);  
    }

     public function productRatingShort(Product $product, ManagerRegistry $doctrine)
    {
        $product_repository = $doctrine->getRepository(Product::class);

        $avg_rating = $product_repository->getAvgRating($product);

        return $this->render('default/_rating_short.html.twig', [
            'rating' => floor($avg_rating)
        ]);  
    }

}
