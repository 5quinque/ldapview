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
        $list = $hostRepository->getList($page_no);

        return $this->render('host/index.html.twig', [
            'hosts' => $list["items"],
            'page_count' => $list["page_count"],
            'host_count' => $list["item_count"],
            'limit' => $list["page_size"],
        ]);
    }

    /**
     * @Route("/{name}", name="host_show", methods={"GET"})
     */
    public function show(Host $host): Response
    {
        $netgroups = $host->getNetgroups();
        $sudoGroups = $host->getSudoGroup();

        $people = [];
        $child_netgroup_arr = [];
        foreach ($netgroups as $netgroup) {
            $people[$netgroup->getName()] = $netgroup->getPeople()->toArray();
            $child_netgroup_arr[] = $netgroup->getChildNetgroup();
        }
        

        $child_people = [];
        foreach ($child_netgroup_arr as $child_netgroups) {
            foreach ($child_netgroups as $child_netgroup) {
                $child_people[$child_netgroup->getName()] = $child_netgroup->getPeople()->toArray();
            }
        }

        return $this->render('host/show.html.twig', [
            'host' => $host,
            'sudo_groups' => $sudoGroups,
            'netgroups' => $netgroups,
            'people' => $people,
            'child_netgroup_arr' => $child_netgroup_arr,
            'child_people' => $child_people,
        ]);
    }
}
