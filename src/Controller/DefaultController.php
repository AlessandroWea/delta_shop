<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Category;

class DefaultController extends AbstractController
{


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

    public function categoryListTabSwitchers(ManagerRegistry $doctrine)
    {
        $categories = $doctrine->getRepository(Category::class)->findBy(['Parent'=>null]);

        return $this->render('default/_category_list_tab_switchers.html.twig', [
            'categories' => $categories,
        ]); 
    }
}