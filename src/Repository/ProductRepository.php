<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineExtensions\Query\Mysql\Rand;

use Doctrine\ORM\Query\ResultSetMapping;
/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Product $entity, bool $flush = true): void
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
    public function remove(Product $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getCommentsCountByRating(Product $entity, int $rating)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(*) FROM product p
            JOIN comment c ON c.product_id = p.id
            WHERE p.id = :p_id AND c.rating = :rating
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['p_id' => $entity->getId(), 'rating' => $rating]);

        return $resultSet->fetchAllAssociative()[0]['COUNT(*)'];
    }

    public function getAllCategories(Product $entity)
    {
        $categories = array();
        $category = $entity->getCategory();
        do {
            array_push($categories, $category);
            $category = $category->getParent();
        } while ($category !== null);


        return array_reverse($categories);
    }

    public function getProductsByCategories(array $categories, $offset = 0 ,$limit = 1)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            WHERE p.category IN (:cat)
            '
        )
        ->setParameter('cat', $categories)
        ->setFirstResult($offset)
        ->setMaxResults($limit);
        
        // returns an array of Product objects
        return $query->getResult();
    }

    public function getRecentProductsByCategories(array $categories, $offset = 0, $limit = 1) : array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            WHERE p.category IN (:cat)
            ORDER BY p.created DESC
            '
        )
        ->setParameter('cat', $categories)
        ->setFirstResult($offset)
        ->setMaxResults($limit);
        // returns an array of Product objects
        return $query->getResult();
    }

    public function getRecentProducts($offset, $limit = 1) : array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            ORDER BY p.created DESC
            '
        )
        ->setFirstResult($offset)
        ->setMaxResults($limit);
        // returns an array of Product objects
        return $query->getResult();
    }

    private function getRandomProductIdsByCategoryIds(array $category_ids, $limit = 1) : array
    {
        $conn = $this->getEntityManager()->getConnection();
        $cids = implode(',',$category_ids);

        $sql = '
            (SELECT p.id FROM product p
            JOIN category c ON p.category_id = c.id
            WHERE c.id IN (' . $cids . ')
            ORDER BY RAND()) LIMIT ' . $limit;

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        $set = $resultSet->fetchAllAssociative();
        $product_ids = array();

        foreach($set as $id)
        {
            array_push($product_ids, $id['id']);
        }
        return $product_ids;
    }

    public function getRandomProductsByCategoryIds(array $category_ids, $limit = 1) : array
    {
        $product_ids = $this->getRandomProductIdsByCategoryIds($category_ids, $limit);

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            WHERE p.id IN (:ids)
            '
        )
        ->setParameter('ids', $product_ids);

        return $query->getResult();
    }

    public function getRandomProducts($limit = 1) : array
    {
        $entityManager = $this->getEntityManager();

        $conn = $entityManager->getConnection();

        $sql = '(SELECT p.id FROM product p ORDER BY RAND()) LIMIT ' . $limit;
        $stmt = $conn->prepare($sql);

        $resultSet = $stmt->executeQuery();
        $set = $resultSet->fetchAllAssociative();

        $product_ids = array();

        foreach($set as $id)
        {
            array_push($product_ids, $id['id']);
        }

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            WHERE p.id IN (:ids)
            '
        )
        ->setParameter('ids', $product_ids);

        return $query->getResult();

    }

    public function getAll($offset, $limit)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p FROM App\Entity\Product p'
        )
        ->setFirstResult($offset)
        ->setMaxResults($limit);

        return $query->getResult();
    }

    public function getCountOfAllProducts()
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountOfProductsByCategoryIds(array $ids)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT count(p) FROM App\Entity\Product p
            WHERE p.category IN (:ids)'
        )
        ->setParameter('ids', $ids);

        return $query->getSingleScalarResult();
    }

    public function search($query, $offset, $limit)
    {
        return $this->createQueryBuilder('p')
                    ->where('p.name LIKE :query')
                    ->setParameter('query', '%' . $query . '%')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    public function searchCount($query)
    {
        return $this->createQueryBuilder('p')
                    ->select('count(p)')
                    ->where('p.name LIKE :query')
                    ->setParameter('query', '%' . $query . '%')
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function searchNewest($query, $offset, $limit)
    {
        return $this->createQueryBuilder('p')
                    ->where('p.name LIKE :query')
                    ->setParameter('query', '%' . $query . '%')
                    ->orderBy('p.created', 'DESC')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    public function searchByCategoryIds($query, $ids ,$offset, $limit)
    {
        return $this->createQueryBuilder('p')
                    ->where('p.name LIKE :query')
                    ->andWhere('p.category IN (:ids)')
                    ->setParameter('query', '%' . $query . '%')
                    ->setParameter('ids', $ids)
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    public function searchCountByCategoryIds($query, $ids)
    {
        return $this->createQueryBuilder('p')
                    ->select('count(p)')
                    ->where('p.name LIKE :query')
                    ->andWhere('p.category IN (:ids)')
                    ->setParameter('query', '%' . $query . '%')
                    ->setParameter('ids', $ids)
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function searchNewestByCategoryIds($query, $ids, $offset, $limit)
    {
        return $this->createQueryBuilder('p')
                    ->where('p.name LIKE :query')
                    ->andWhere('p.category IN (:ids)')
                    ->orderBy('p.created', 'DESC')
                    ->setParameter('query', '%' . $query . '%')
                    ->setParameter('ids', $ids)
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }
    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    
    // public function findByExampleField($value)
    // {
    //     return $this->createQueryBuilder('p')
    //         ->andWhere('p.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('p.id', 'ASC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }
    

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
