<?php

namespace App\Controller;

use App\Entity\Sudo;
use App\Repository\SudoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use App\Request\SudoParmConverter; [todo]
use App\Service\LdapService;

/**
 * @Route("/sudo")
 */
class SudoController extends AbstractController
{
    /**
     * @Route("/{page_no<\d+>?0}", name="sudo_index", methods={"GET"})
     */
    public function index(SudoRepository $sudoRepository, int $page_no, LdapService $ldapService): Response
    {
        $list = $sudoRepository->getList($page_no);

        return $this->render('sudo/index.html.twig', [
            'sudogroups' => $list["items"],
            'page_count' => $list["page_count"],
            'sudo_count' => $list["item_count"],
            'limit' => $list["page_size"],
        ]);
    }

    /**
     * @Route("/{name}", name="sudo_show", methods={"GET"})
     */
    public function show(Sudo $sudo): Response
    {
        $people = $sudo->getUser();
        $hosts = $sudo->getHost();
        $commands = $sudo->getSudoCommands();

        // $child_netgroups = $netgroup->getChildNetgroup();

        // $child_people = [];
        // foreach ($child_netgroups as $child_netgroup) {
        //     $child_people[$child_netgroup->getName()] = $child_netgroup->getPeople()->toArray();
        // }

        return $this->render('sudo/show.html.twig', [
            'sudo' => $sudo,
            'people' => $people,
            'hosts' => $hosts,
            'commands' => $commands
            // 'child_netgroups' => $child_netgroups,
            // 'child_people' => $child_people,
        ]);
    }

}
