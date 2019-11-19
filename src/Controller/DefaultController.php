<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HostRepository;
use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;
use App\Repository\SudoRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(
        HostRepository $hostRepository,
        NetgroupRepository $netgroupRepository,
        PeopleRepository $peopleRepository,
        SudoRepository $sudoRepository
        )
    {
        return $this->render('default/index.html.twig', [
            'host_count' => $this->getEntityCount($hostRepository),
            'netgroup_count' => $this->getEntityCount($netgroupRepository),
            'people_count' => $this->getEntityCount($peopleRepository),
            'sudo_count' => $this->getEntityCount($sudoRepository)
        ]);
    }

    private function getEntityCount($repository)
    {
        $query = $repository->createQueryBuilder('p')->getQuery();
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        return $paginator->count();
    }
}
