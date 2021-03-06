<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Category $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Category $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getAllParents(Category $entity) : array
    {
        $parents = array();
        $parent = $entity->getParent();
        while($parent !== null)
        {
            array_push($parents, $parent);
            $parent = $parent->getParent();
        }

        return array_reverse($parents);
    }

    private function _getAllLastSubCategoryIds(Category $entity)
    {
        $arr = '';

        $children = $entity->getChildren();

        foreach($children as $child)
        {
            if(count($child->getChildren()) === 0)
                $arr .= $child->getId() . ';';
            $arr .= $this->_getAllLastSubCategoryIds($child);
        }


        return $arr;
    }

    public function getAllLastSubCategoryIds(Category $entity)
    {
        if(count($entity->getChildren()) > 0)
        {
            $str_ids = $this->_getAllLastSubCategoryIds($entity);
            $subs = explode(';',$str_ids);
            array_pop($subs);    
        }
        else // if it doesn't have children -> this is the last sub itself
        {
            $subs = [$entity->getId()];
        }


        return $subs;
    }

    public function getAllLastSubCategories(Category $entity)
    {
        $sub_ids = $this->getAllLastSubCategoryIds($entity);
        return $this->findByIds($sub_ids);
    }

    public function findByIds(Array $ids) : array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Category c
            WHERE c.id IN (:ids)
            '
        )
        ->setParameter('ids', $ids);
        // returns an array of Product objects
        return $query->getResult();
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
