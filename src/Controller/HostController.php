<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HostRepository;
use App\Entity\Host;

/**
 * @Route("/host")
 */
class HostController extends AbstractController
{
    /**
     * @Route("/{page_no<\d+>?0}", name="host_index")
     */
    public function index(HostRepository $hostRepository, int $page_no)
    {
        $page_size = 10;

        if (isset($_GET["limit"])) {
            if (preg_match('/^\d+$/', $_GET["limit"]))
                $page_size = $_GET["limit"];
        }

        $query = $hostRepository->createQueryBuilder('p')->getQuery();
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, $fetchJoinCollection = true);

        $host_count = $paginator->count();
        $page_count = (int) floor($host_count / $page_size);
        $paginator->getQuery()->setFirstResult($page_size * $page_no)->setMaxResults($page_size)->getArrayResult();

        $hosts = [];
        foreach ($paginator as $host) {
            $hosts[] = $host;
        }

        return $this->render('host/index.html.twig', [
            'hosts' => $hosts,
            'page_count' => $page_count,
            'host_count' => $host_count,
            'limit' => $page_size,
        ]);
    }

    /**
     * @Route("/{name}", name="host_show", methods={"GET"})
     */
    public function show(Host $host): Response
    {
        $netgroups = $host->getNetgroups();

        return $this->render('host/show.html.twig', [
            'host' => $host,
            'netgroups' => $netgroups,
        ]);
    }
}
