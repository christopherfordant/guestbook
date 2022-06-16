<?php

namespace App\Repository;

use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conference>
 *
 * @method Conference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conference[]    findAll()
 * @method Conference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    public function add(Conference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Conference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public const PAGINATOR_PER_PAGE = 4;

    public function getConferencePaginator(string $year, string $city, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c');
        if ($year) {
            $query = $query->andWhere('c.year = :year')
                ->setParameter('year', $year);
        }
        if ($city) {
            $query = $query->andWhere('c.city = :city')
                ->setParameter('city', $city);
        }
        $query = $query->orderBy('c.year', 'DESC')
            ->addOrderBy('c.city')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
    }

    public function getListYear()
    {
        $years = [];
        foreach ($this->createQueryBuilder('c')
                     ->select('c.year')
                     ->distinct(true)
                     ->orderBy('c.year', 'ASC')
                     ->getQuery()
                     ->getResult() as $cols) {
            $years[] = $cols['year'];
        }
        return $years;
    }

    public function getListCity()
    {
        $citys = [];
        foreach ($this->createQueryBuilder('c')
                     ->select('c.city')
                     ->distinct(true)
                     ->orderBy('c.city', 'ASC')
                     ->getQuery()
                     ->getResult() as $cols) {
            $citys[] = $cols['city'];
        }
        return $citys;
    }


//    /**
//     * @return Conference[] Returns an array of Conference objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Conference
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
