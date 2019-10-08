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

    public function findByLike($query)
    {
        return $this->createQueryBuilder('n')
           ->where('n.name LIKE :query')
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

        $netgroup_count = $paginator->count();
        $page_count = (int)floor($netgroup_count / $page_size);
        $paginator->getQuery()->setFirstResult($page_size * $page_no)->setMaxResults($page_size)->getArrayResult();

        $netgroups = [];
        foreach ($paginator as $netgroup) {
            $netgroups[] = $netgroup;
        }

        return [
            "netgroups" => $netgroups,
            "page_count" => $page_count,
            "netgroup_count" => $netgroup_count,
            "page_size" => $page_size
        ];
    }

}
