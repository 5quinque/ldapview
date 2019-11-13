<?php

namespace App\Controller;

use App\Entity\Netgroup;
use App\Repository\NetgroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Request\NetgroupParmConverter;
use App\Service\LdapService;

/**
 * @Route("/netgroup")
 */
class NetgroupController extends AbstractController
{
    /**
     * @Route("/{page_no<\d+>?0}", name="netgroup_index", methods={"GET"})
     */
    public function index(NetgroupRepository $netgroupRepository, int $page_no, LdapService $ldapService): Response
    {
        $list = $netgroupRepository->getList($page_no);

        return $this->render('netgroup/index.html.twig', [
            'netgroups' => $list["items"],
            'page_count' => $list["page_count"],
            'netgroup_count' => $list["item_count"],
            'limit' => $list["page_size"],
        ]);
    }

    /**
     * @Route("/{name}", name="netgroup_show", methods={"GET"})
     */
    public function show(Netgroup $netgroup): Response
    {
        $people = $netgroup->getPeople();
        $hosts = $netgroup->getHost();
        $child_netgroups = $netgroup->getChildNetgroup();

        $child_people = [];
        foreach ($child_netgroups as $child_netgroup) {
            $child_people[$child_netgroup->getName()] = $child_netgroup->getPeople()->toArray();
        }

        return $this->render('netgroup/show.html.twig', [
            'netgroup' => $netgroup,
            'people' => $people,
            'hosts' => $hosts,
            'child_netgroups' => $child_netgroups,
            'child_people' => $child_people,
        ]);
    }

}
