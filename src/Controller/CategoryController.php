<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use App\Entity\Comment;
use App\Entity\Category;

use App\Utils\Utils;

class CategoryController extends AbstractController
{

    const DEFAULT_NUMBER_OF_PRODUCTS_ON_PAGE = 3;
    const NUMBER_OF_RANDOM_PRODUCTS_IN_ASIDE_LIST = 3;
    const NUMBER_OF_PAGE_LINKS = 4;

    private $order_options = [
        'Default', 'Newest'
    ];

    private $count_options = [
        3,6,9,12,20
    ];

    public function category(int $id, Request $request, ManagerRegistry $doctrine, Utils $utils)
    {
        //getting all repositories
        $category_repository = $doctrine->getRepository(Category::class);
        $product_repository = $doctrine->getRepository(Product::class);

        // getting GET-variables from query
        $current_page = $request->query->get('page') ?? 1;
        $order = $request->query->get('orderBy') ?? 'Default';
        $number_of_products_per_page = $request->query->get('count') ?? self::DEFAULT_NUMBER_OF_PRODUCTS_ON_PAGE;

        // throw an exception if one of GET-variables is not valid
        if(!in_array($number_of_products_per_page, $this->count_options) || !in_array($order, $this->order_options))
            throw $this->createNotFoundException('The page does not exist');

        $offset = $number_of_products_per_page * ($current_page - 1);

        //if category is not found -> display all products
        $current_category = $category_repository->find($id);
        if($current_category === null)
        {
            if($order === 'Default')
            {
                $products = $product_repository->getAll($offset, $number_of_products_per_page);
            }
            else if($order === 'Newest')
            {
                $products = $product_repository->getRecentProducts($offset, $number_of_products_per_page);
            }

            $number_of_products = $product_repository->getCountOfAllProducts();
            $number_of_pages = (int)ceil($number_of_products / $number_of_products_per_page);
        }
        else // display products of particular category or categories
        {
            $sub_category_ids = $category_repository->getAllLastSubCategoryIds($current_category);

            if($order === 'Default')
            {
                $products = $product_repository->getProductsByCategories($sub_category_ids, $offset, $number_of_products_per_page);
            }
            else if($order === 'Newest')
            {
                $products = $product_repository->getRecentProductsByCategories($category_repository->findByIds($sub_category_ids), $offset, $number_of_products_per_page);
            }

            $number_of_products = $product_repository->getCountOfProductsByCategoryIds($sub_category_ids);
            $number_of_pages = (int)ceil($number_of_products / $number_of_products_per_page);
        }
        
        if($number_of_pages == 0) $number_of_pages = 1;

        if($current_page > $number_of_pages)
            throw $this->createNotFoundException('The page does not exist');

        $current_breadcrumb = ($current_category) ? $current_category->getName() : 'All categories';
        $breadcrumbs = ($current_category) ? $utils->convertCategoriesIntoBreadcrumbs($category_repository->getAllParents($current_category)) : array();

        $top_categories = $category_repository->findBy(['Parent'=>null]);
        $catalog = $this->makeCatalog($top_categories,  ($current_category) ? $current_category->getName() : '-');

        return $this->render('category/category.html.twig', [
            'products' => $products,
            'number_of_products' => $number_of_products,

            'category_id' => $id,

            'catalog' => $catalog,

            'number_of_page_links' => (self::NUMBER_OF_PAGE_LINKS > $number_of_pages) ? $number_of_pages : self::NUMBER_OF_PAGE_LINKS,
            'number_of_products_per_page' => $number_of_products_per_page,
            'number_of_pages' => $number_of_pages,

            'count_options' => $this->count_options,
            'order_options' => $this->order_options,

            'current_page' => $current_page,
            'current_order' => $order,

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $current_breadcrumb,
        ]);
    }

    public function randomProductAsideList(ManagerRegistry $doctrine)
    {
        $product_repository = $doctrine->getRepository(Product::class);

        $products = $product_repository->getRandomProducts(self::NUMBER_OF_RANDOM_PRODUCTS_IN_ASIDE_LIST);

        return $this->render('category/_random_product_aside_list.html.twig', [
            'products' => $products
        ]);
    }

    //$parents = top level category objects,
    //$activee = name of active category
    public function makeCatalog($parents, $active ,$level = 0)
    {
        $html = "<ul class='catalog-$level'>";

        foreach($parents as $parent)
        {
            if($parent->getName() === $active)
                $html .= '<li class="active"><a href="' . $this->generateUrl('category', ['id' => $parent->getId()]) . '">' . $parent->getName() . '</a>';
            else
                $html .= '<li><a href="' . $this->generateUrl('category', ['id' => $parent->getId()]) . '">' . $parent->getName() . '</a>';

            if(count($parent->getChildren()) !== 0)
            {
                $level++;
                $html .= $this->makeCatalog($parent->getChildren(), $active, $level);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}
