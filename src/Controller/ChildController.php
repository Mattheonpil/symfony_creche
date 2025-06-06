<?php

namespace App\Controller;

use App\Entity\Child;
use App\Form\ChildForm;
use App\Repository\ChildRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/child')]
final class ChildController extends AbstractController
{
    #[Route(name: 'app_child_index', methods: ['GET'])]
    public function index(ChildRepository $childRepository): Response
    {
        return $this->render('child/index.html.twig', [
            'children' => $childRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_child_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $child = new Child();
        $form = $this->createForm(ChildForm::class, $child);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($child);
            $entityManager->flush();

            return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('child/new.html.twig', [
            'child' => $child,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_child_show', methods: ['GET'])]
    public function show(Child $child): Response
    {
        return $this->render('child/show.html.twig', [
            'child' => $child,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_child_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Child $child, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChildForm::class, $child);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('child/edit.html.twig', [
            'child' => $child,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_child_delete', methods: ['POST'])]
    public function delete(Request $request, Child $child, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$child->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($child);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
    }
}
