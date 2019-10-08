<?php

namespace App\Controller;

use App\Entity\Netgroup;
use App\Form\NetgroupType;
use App\Repository\NetgroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Request\NetgroupParmConverter;
use App\Service\LdapNetgroupService;
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
        //$ldapService->findAll(["ou" => "netgroup", "objectClass" => "nisNetgroup"]);

        $list = $netgroupRepository->getList($page_no);
        
        return $this->render('netgroup/index.html.twig', [
            'netgroups' => $list["netgroups"],
            'page_count' => $list["page_count"],
            'netgroup_count' => $list["netgroup_count"],
            'limit' => $list["page_size"],
        ]);
    }

    /**
     * @Route("/new", name="netgroup_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $netgroup = new Netgroup();
        $form = $this->createForm(NetgroupType::class, $netgroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($netgroup);
            $entityManager->flush();

            return $this->redirectToRoute('netgroup_show', ['name' => $netgroup->getName()]);
        }

        return $this->render('netgroup/new.html.twig', [
            'netgroup' => $netgroup,
            'form' => $form->createView(),
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
        foreach($child_netgroups as $child_netgroup) {
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

    /**
     * @Route("/{name}/edit", name="netgroup_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Netgroup $netgroup, LdapNetgroupService $ldapNetgroupService): Response
    {
        $form = $this->createForm(NetgroupType::class, $netgroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $ldapNetgroupService->persist($person);

            return $this->redirectToRoute('netgroup_show', ['name' => $netgroup->getName()]);
        }

        return $this->render('netgroup/edit.html.twig', [
            'netgroup' => $netgroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{name}", name="netgroup_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Netgroup $netgroup): Response
    {
        if ($this->isCsrfTokenValid('delete' . $netgroup->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($netgroup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('netgroup_index');
    }
}
