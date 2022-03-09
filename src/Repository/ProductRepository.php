<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getRecentProductsByCategories(array $categories) : array
    {
        $entityManager = $this->getEntityManager();
        $time = time() - (30 * 24 * 60 * 60);
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Category c
           JOIN App\Entity\Product p
            WHERE p.category IN (:cat) AND p.created >:time
            '
        )
        ->setParameter('cat', $categories)
        ->setParameter('time', $time);

        // returns an array of Product objects
        return $query->getResult();
    }
    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

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
