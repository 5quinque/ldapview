<?php

namespace App\Repository;

use App\Entity\People;
use App\Repository\AppRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method People|null find($id, $lockMode = null, $lockVersion = null)
 * @method People|null findOneBy(array $criteria, array $orderBy = null)
 * @method People[]    findAll()
 * @method People[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleRepository extends AppRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, People::class);
    }

    public function findByLike($query)
    {
        return $this->createQueryBuilder('p')
           ->where('p.uid LIKE :query')
           ->setParameter('query', "%$query%")
           ->getQuery()
           ->getResult();
    }

}
