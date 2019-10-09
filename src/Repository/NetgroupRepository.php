<?php

namespace App\Repository;

use App\Entity\Netgroup;
use App\Repository\AppRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Netgroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method Netgroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method Netgroup[]    findAll()
 * @method Netgroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NetgroupRepository extends AppRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Netgroup::class);
    }

    public function findByLike($query)
    {
        return $this->createQueryBuilder('n')
           ->where('n.name LIKE :query')
           ->setParameter('query', "%$query%")
           ->getQuery()
           ->getResult();
    }

}
