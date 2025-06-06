<?php

namespace App\Controller;

use App\Entity\Recovery;
use App\Form\RecoveryForm;
use App\Repository\RecoveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recovery')]
final class RecoveryController extends AbstractController
{
    #[Route(name: 'app_recovery_index', methods: ['GET'])]
    public function index(RecoveryRepository $recoveryRepository): Response
    {
        return $this->render('recovery/index.html.twig', [
            'recoveries' => $recoveryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_recovery_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recovery = new Recovery();
        $form = $this->createForm(RecoveryForm::class, $recovery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($recovery);
            $entityManager->flush();

            return $this->redirectToRoute('app_recovery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recovery/new.html.twig', [
            'recovery' => $recovery,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recovery_show', methods: ['GET'])]
    public function show(Recovery $recovery): Response
    {
        return $this->render('recovery/show.html.twig', [
            'recovery' => $recovery,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recovery_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recovery $recovery, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RecoveryForm::class, $recovery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_recovery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recovery/edit.html.twig', [
            'recovery' => $recovery,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recovery_delete', methods: ['POST'])]
    public function delete(Request $request, Recovery $recovery, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recovery->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($recovery);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recovery_index', [], Response::HTTP_SEE_OTHER);
    }
}
