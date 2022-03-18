<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Category;
use App\Entity\Product;

use App\Utils\PageSettings;

class SearchController extends AbstractController
{

    public function search(Request $request, ManagerRegistry $doctrine): Response
    {
        //getting all repositories
        $category_repository = $doctrine->getRepository(Category::class);
        $product_repository = $doctrine->getRepository(Product::class);

        //getting GET-variables
        $query = $request->query->get('q');
        if(!$query)
            throw $this->createNotFoundException('The page does not exist');

        $category_id = $request->query->get('category') ?? 0;
        $page = $request->query->get('page') ?? 1;
        $order = $request->query->get('orderBy') ?? 'Default';
        $number_of_products_per_page = $request->query->get('count') ?? PageSettings::DEFAULT_NUMBER_OF_PRODUCTS_PER_PAGE;

        $valid = PageSettings::validate([
            'count_option' => $number_of_products_per_page,
            'order_option' => $order,
        ]);

        // throw an exception if one of GET-variables is not valid
        if(!$valid) throw $this->createNotFoundException('The page does not exist');      

        // if(!in_array($number_of_products_per_page, PageSettings::getCountOptions()) || !in_array($order, PageSettings::getOrderOptions()))
        //     throw $this->createNotFoundException('The page does not exist');      

        $offset = $number_of_products_per_page * ($page - 1);

        // search in all categories
        if($category_id == 0)
        {
            if($order === 'Default')
            {
                $products = $product_repository->search($query, $offset, $number_of_products_per_page);
            }
            else if($order === 'Newest')
            {
                $products = $product_repository->searchNewest($query, $offset, $number_of_products_per_page);
            }

            $number_of_products = $product_repository->searchCount($query);
        }
        //search in particular categories
        else
        {
            $category_of_search = $category_repository->find($category_id);
            $sub_category_ids = $category_repository->getAllLastSubCategoryIds($category_of_search);

            if($order === 'Default')
            {
                $products = $product_repository->searchByCategoryIds($query, $sub_category_ids, $offset, $number_of_products_per_page);
            }
            else if($order === 'Newest')
            {
                $products = $product_repository->searchNewestByCategoryIds($query, $sub_category_ids, $offset, $number_of_products_per_page);
            }

            $number_of_products = $product_repository->searchCountByCategoryIds($query, $sub_category_ids);
        }


        $number_of_pages = (int)ceil($number_of_products / $number_of_products_per_page);
        if($number_of_pages == 0) $number_of_pages = 1;

        if($page > $number_of_pages)
            throw $this->createNotFoundException('The page does not exist');

        return $this->render('search/search.html.twig', [

            'products' => $products,
            'number_of_products' => $number_of_products,

            'query' => $query,

            'number_of_page_links' => (PageSettings::NUMBER_OF_PAGE_LINKS > $number_of_pages) ? $number_of_pages : PageSettings::NUMBER_OF_PAGE_LINKS,
            'number_of_products_per_page' => $number_of_products_per_page,
            'number_of_pages' => $number_of_pages,

            'count_options' => PageSettings::getCountOptions(),
            'order_options' => PageSettings::getOrderOptions(),

            'current_page' => $page,
            'current_order' => $order,
        ]);
    }

    public function searchBar(int $id = 0, string $query = '', ManagerRegistry $doctrine, Request $request)
    {
        $category_repository = $doctrine->getRepository(Category::class);

        $categories = $category_repository->findBy(['Parent' => null]);
        return $this->render('search/_search_bar.html.twig', [
            'categories' => $categories,
            'category_id' => $id,
            'query' => $query,
        ]);    
    }
}

