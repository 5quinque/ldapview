<?php

namespace App\Controller;

use App\Entity\Netgroup;
use App\Form\NetgroupType;
use App\Repository\NetgroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/netgroup")
 */
class NetgroupController extends AbstractController
{
    /**
     * @Route("/", name="netgroup_index", methods={"GET"})
     */
    public function index(NetgroupRepository $netgroupRepository): Response
    {
        return $this->render('netgroup/index.html.twig', [
            'netgroups' => $netgroupRepository->findAll(),
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

            return $this->redirectToRoute('netgroup_index');
        }

        return $this->render('netgroup/new.html.twig', [
            'netgroup' => $netgroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="netgroup_show", methods={"GET"})
     */
    public function show(Netgroup $netgroup): Response
    {
        return $this->render('netgroup/show.html.twig', [
            'netgroup' => $netgroup,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="netgroup_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Netgroup $netgroup): Response
    {
        $form = $this->createForm(NetgroupType::class, $netgroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('netgroup_index');
        }

        return $this->render('netgroup/edit.html.twig', [
            'netgroup' => $netgroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="netgroup_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Netgroup $netgroup): Response
    {
        if ($this->isCsrfTokenValid('delete'.$netgroup->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($netgroup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('netgroup_index');
    }
}
