<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HostRepository;
use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(HostRepository $hostRepository, NetgroupRepository $netgroupRepository, PeopleRepository $peopleRepository)
    {
        $host_query = $hostRepository->createQueryBuilder('p')->getQuery();
        $host_paginator = new Paginator($host_query, $fetchJoinCollection = true);
        $host_count = $host_paginator->count();

        $netgroup_query = $netgroupRepository->createQueryBuilder('p')->getQuery();
        $netgroup_paginator = new Paginator($netgroup_query, $fetchJoinCollection = true);
        $netgroup_count = $netgroup_paginator->count();

        $people_query = $peopleRepository->createQueryBuilder('p')->getQuery();
        $people_paginator = new Paginator($people_query, $fetchJoinCollection = true);
        $people_count = $people_paginator->count();


        return $this->render('default/index.html.twig', [
            'host_count' => $host_count,
            'netgroup_count' => $netgroup_count,
            'people_count' => $people_count,
        ]);
    }
}
