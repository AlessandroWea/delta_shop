<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\Category;

use App\Utils\Utils;

class MainController extends AbstractController
{
    /**
     * @throws Exception
     */
    public function homepage(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Category::class)->findBy(['Parent'=>null]);

        return $this->render('main/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    public function newProductsList(Category $category, ContainerInterface $container, ManagerRegistry $doctrine)
    {
        $category_repository = $doctrine->getRepository(Category::class);
        $arr_of_last_sub_category_ids = $category_repository->getAllLastSubCategoryIds($category);

        $categories = [];

        foreach($arr_of_last_sub_category_ids as $id)
        {
            $categories[] = $category_repository->find($id);
        }

        $product_repository = $doctrine->getRepository(Product::class);
        $products = $product_repository->getRecentProductsByCategories($categories, 0, 6);

        // $formats = $container->get('sonata.media.pool')->getFormatNamesByContext($products[0]->getImage()->getContext());
        // dd($formats);

        // $provider = $container->get($products[0]->getImage()->getProviderName());
        // $url = $provider->generatePublicUrl($products[0]->getImage(), 'product_small');

        // dd($url);

        return $this->render('main/_products_list.html.twig', [
            'products' => $products
        ]);  
    }

    public function randomProductsList(Category $category, ManagerRegistry $doctrine)
    {
        $category_repository = $doctrine->getRepository(Category::class);
        $product_repository = $doctrine->getRepository(Product::class);

        $arr_of_last_sub_category_ids = $category_repository->getAllLastSubCategoryIds($category);

        $products = $product_repository->getRandomProductsByCategoryIds($arr_of_last_sub_category_ids, 2);
        return $this->render('main/_products_list.html.twig', [
            'products' => $products
        ]);  
    }

}
