<?php

namespace App\Repository;

use App\Entity\SudoCommand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SudoCommand|null find($id, $lockMode = null, $lockVersion = null)
 * @method SudoCommand|null findOneBy(array $criteria, array $orderBy = null)
 * @method SudoCommand[]    findAll()
 * @method SudoCommand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SudoCommandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SudoCommand::class);
    }

    // /**
    //  * @return SudoCommand[] Returns an array of SudoCommand objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SudoCommand
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
