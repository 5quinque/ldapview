<?php

namespace App\Controller;

use App\Entity\People;
use App\Form\PeopleType;
use App\Repository\PeopleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Request\PeopleParmConverter;
use App\Service\LdapPeopleService;
use App\Service\LdapNetgroupService;
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
        // set_time_limit(0);
        // $ldapService->findAll(["ou" => "people", "objectClass" => "posixAccount"]);

        $list = $peopleRepository->getList($page_no);

        return $this->render('people/index.html.twig', [
            'people' => $list["items"],
            'page_count' => $list["page_count"],
            'people_count' => $list["item_count"],
            'limit' => $list["page_size"],
        ]);
    }

    /**
     * @Route("/new", name="people_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $person = new People();
        $form = $this->createForm(PeopleType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('people_show', ['uid' => $person->getUid()]);
        }

        return $this->render('people/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uid}", name="people_show", methods={"GET"})
     */
    public function show(People $person): Response
    {
        $netgroups = $person->getNetgroup();
        
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
            'netgroups' => $netgroups->toArray(),
            'hosts' => $hosts,
            'parent_netgroup_arr' => $parent_netgroup_arr,
            'parent_hosts' => $parent_hosts,
        ]);
    }

    /**
     * @Route("/{uid}/edit", name="people_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,
        People $person,
        LdapPeopleService $ldapPeopleService,
        LdapNetgroupService $ldapNetgroupService): Response
    {
        $form = $this->createForm(PeopleType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $previousNetgroups = unserialize($form->get('netgroups_hidden')->getData());
            
            $this->getDoctrine()->getManager()->flush();
            $ldapPeopleService->persist($person);
            $ldapNetgroupService->persistNetgroups($person, $previousNetgroups);

            return $this->redirectToRoute('people_show', ['uid' => $person->getUid()]);
        }

        return $this->render('people/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uid}", name="people_delete", methods={"DELETE"})
     */
    public function delete(Request $request, People $person): Response
    {
        if ($this->isCsrfTokenValid('delete' . $person->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($person);
            $entityManager->flush();
        }

        return $this->redirectToRoute('people_index');
    }
}
