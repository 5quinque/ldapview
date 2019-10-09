<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

abstract class AppRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry, $entityClass) {
        parent::__construct($registry, $entityClass);
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

        $item_count = $paginator->count();
        $page_count = (int) floor($item_count / $page_size);
        $paginator->getQuery()->setFirstResult($page_size * $page_no)->setMaxResults($page_size)->getArrayResult();

        $items = [];
        foreach ($paginator as $item) {
            $items[] = $item;
        }

        return [
            "items" => $items,
            "page_count" => $page_count,
            "item_count" => $item_count,
            "page_size" => $page_size
        ];
    }
}