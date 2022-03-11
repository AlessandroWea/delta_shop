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

        $top_categories = $category_repository->findBy(['Parent'=>null]);

        //if category is not found -> display all products
        $current_category = $category_repository->find($id);
        if($current_category === null)
        {
            $products = $product_repository->findAll();

            $breadcrumbs = array();
            $current_breadcrumb = 'All categories';

            $catalog = $this->makeCatalog($top_categories, '-');

        }
        else
        {
            $subCategories = $category_repository->getAllLastSubCategoryIds($current_category);
            $products = $product_repository->getRecentProductsByCategories($subCategories, 10);

            $parents_of_current_category = $category_repository->getAllParents($current_category);
            $breadcrumbs = $utils->convertCategoriesIntoBreadcrumbs($parents_of_current_category);

            $current_breadcrumb = $current_category->getName();

            $catalog = $this->makeCatalog($top_categories, $current_category->getName());

        }


        return $this->render('category/category.html.twig', [
            'products' => $products,

            'catalog' => $catalog,

            'breadcrumbs' => $breadcrumbs,
            'current_breadcrumb' => $current_breadcrumb,
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
