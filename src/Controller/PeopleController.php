<?php

namespace App\Controller;

use App\Entity\People;
use App\Repository\PeopleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Request\PeopleParmConverter;
use App\Service\LdapService;

/**
 * @Route("/people")
 */
class PeopleController extends AbstractController
{
    /**
     * @Route("/{page_no<\d+>?0}", name="people_index", methods={"GET"})
     */
    public function index(PeopleRepository $peopleRepository, int $page_no, LdapService $ldapService): Response
    {
        $list = $peopleRepository->getList($page_no);

        return $this->render('people/index.html.twig', [
            'people' => $list["items"],
            'page_count' => $list["page_count"],
            'people_count' => $list["item_count"],
            'limit' => $list["page_size"],
        ]);
    }

    /**
     * @Route("/{uid}", name="people_show", methods={"GET"})
     */
    public function show(People $person): Response
    {
        $netgroups = $person->getNetgroup();
        $sudoGroups = $person->getSudoGroup();
        
        $hosts = [];
        $parent_netgroup_arr = [];
        foreach ($netgroups as $netgroup) {
            $hosts[$netgroup->getName()] = $netgroup->getHost()->toArray();
            $parent_netgroup_arr[] = $netgroup->getParentNetgroup();
        }

        $parent_hosts = [];
        foreach ($parent_netgroup_arr as $parent_netgroups) {
            foreach ($parent_netgroups as $parent_netgroup) {
                $parent_hosts[$parent_netgroup->getName()] = $parent_netgroup->getHost()->toArray();
            }
        }
        
        return $this->render('people/show.html.twig', [
            'person' => $person,
            'sudo_groups' => $sudoGroups,
            'netgroups' => $netgroups->toArray(),
            'hosts' => $hosts,
            'parent_netgroup_arr' => $parent_netgroup_arr,
            'parent_hosts' => $parent_hosts,
        ]);
    }

}
