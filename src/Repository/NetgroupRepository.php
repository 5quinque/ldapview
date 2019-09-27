<?php

namespace App\Repository;

use App\Entity\Netgroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Netgroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method Netgroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method Netgroup[]    findAll()
 * @method Netgroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NetgroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Netgroup::class);
    }

    // /**
    //  * @return Netgroup[] Returns an array of Netgroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Netgroup
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
