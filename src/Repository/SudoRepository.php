<?php

namespace App\Repository;

use App\Entity\Sudo;
use App\Repository\AppRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Sudo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sudo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sudo[]    findAll()
 * @method Sudo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SudoRepository extends AppRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sudo::class);
    }

    public function findByLike($query)
    {
        return $this->createQueryBuilder('s')
           ->where('s.name LIKE :query')
           ->setParameter('query', "%$query%")
           ->getQuery()
           ->getResult();
    }
}
