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



    public function commentRatingShort(Comment $comment, ManagerRegistry $doctrine)
    {
        $rating = $comment->getRating();

        return $this->render('default/_rating_short.html.twig', [
            'rating' => $rating,
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