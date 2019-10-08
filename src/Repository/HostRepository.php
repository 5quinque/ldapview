<?php

namespace App\Repository;

use App\Entity\Host;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Host|null find($id, $lockMode = null, $lockVersion = null)
 * @method Host|null findOneBy(array $criteria, array $orderBy = null)
 * @method Host[]    findAll()
 * @method Host[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Host::class);
    }

    public function findByLike($query)
    {
        return $this->createQueryBuilder('h')
           ->where('h.name LIKE :query')
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

        $host_count = $paginator->count();
        $page_count = (int) floor($host_count / $page_size);
        $paginator->getQuery()->setFirstResult($page_size * $page_no)->setMaxResults($page_size)->getArrayResult();

        $hosts = [];
        foreach ($paginator as $host) {
            $hosts[] = $host;
        }

        return [
            "hosts" => $hosts,
            "page_count" => $page_count,
            "host_count" => $host_count,
            "page_size" => $page_size
        ];
    }

}
