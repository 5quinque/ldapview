<?php

namespace App\Controller;

use App\Entity\People;
use App\Form\PeopleType;
use App\Repository\PeopleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @Route("/people")
 */
class PeopleController extends AbstractController
{
    /**
     * @Route("/{page_no<\d+>?0}", name="people_index", methods={"GET"})
     */
    public function index(PeopleRepository $peopleRepository, int $page_no): Response
    {
//        $people = $peopleRepository->findBy(
//            [],
//            array('id' => 'ASC'),
//            10,
//            ($page_no * 10),
//            );
        $page_size = 10;

        $query = $peopleRepository->createQueryBuilder('p')->getQuery();
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, $fetchJoinCollection = true);

        $people_count = $paginator->count();
        $page_count = (int) floor($people_count / $page_size);
        $paginator->getQuery()->setFirstResult($page_size * $page_no)->setMaxResults($page_size)->getArrayResult();

        $people = [];
        foreach ($paginator as $person) {
            $people[] = $person;
        }

        return $this->render('people/index.html.twig', [
            'people' => $people,
            'page_count' => $page_count,
            'people_count' => $people_count,
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

            return $this->redirectToRoute('people_index');
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

        return $this->render('people/show.html.twig', [
            'person' => $person,
            'netgroups' => $netgroups->toArray(),
        ]);
    }

    /**
     * @Route("/{uid}/edit", name="people_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, People $person): Response
    {
        $form = $this->createForm(PeopleType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('people_index');
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
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($person);
            $entityManager->flush();
        }

        return $this->redirectToRoute('people_index');
    }
}
