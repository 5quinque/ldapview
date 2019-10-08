<?php

namespace App\Repository;

use App\Entity\People;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method People|null find($id, $lockMode = null, $lockVersion = null)
 * @method People|null findOneBy(array $criteria, array $orderBy = null)
 * @method People[]    findAll()
 * @method People[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleRepository extends ServiceEntityRepository
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

    public function getList(int $page_no)
    {
        $page_size = 10;

        if (isset($_GET["limit"])) {
            if (preg_match('/^\d+$/', $_GET["limit"])) {
                $page_size = $_GET["limit"];
            }
        }

        $query = $this->createQueryBuilder('p')->getQuery();
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $people_count = $paginator->count();
        $page_count = (int)floor($people_count / $page_size);
        $paginator->getQuery()->setFirstResult($page_size * $page_no)->setMaxResults($page_size)->getArrayResult();

        $people = [];
        foreach ($paginator as $person) {
            $people[] = $person;
        }
        
        return [
            "people" => $people,
            "page_count" => $page_count,
            "people_count" => $people_count,
            "page_size" => $page_size
        ];
    }
}
