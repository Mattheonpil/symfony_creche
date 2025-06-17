<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PlanningForm;
use App\Repository\PlanningRepository;
use App\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planning')]
final class PlanningController extends AbstractController
{
    #[Route(name: 'app_planning_index', methods: ['GET'])]
    public function index(PlanningRepository $planningRepository): Response
    {
        return $this->render('planning/index.html.twig', [
            'plannings' => $planningRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_planning_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $planning = new Planning();
        $form = $this->createForm(PlanningForm::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $planning->updateMealStatus();
            $entityManager->persist($planning);
            $entityManager->flush();

            return $this->redirectToRoute('app_planning_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planning/new.html.twig', [
            'planning' => $planning,
            'form' => $form,
        ]);
    }

    #[Route('/day', name: 'app_planning_day', methods: ['GET'])]
    public function planningDay(
        Request $request,
        CalendarRepository $calendarRepository,
        PlanningRepository $planningRepository
    ): Response {
        $dateString = $request->query->get('date');
        $date = $dateString ? new \DateTime($dateString) : new \DateTime();
        $date->setTimezone(new \DateTimeZone('Europe/Paris'));

        // Récupérer la semaine en cours
        $weekStart = clone $date;
        $weekStart->modify('monday this week');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+4 days');

        // Modification de la requête pour la logique inversée
        $weekDays = $calendarRepository->createQueryBuilder('c')
            ->where('c.date >= :start')
            ->andWhere('c.date <= :end')
            ->andWhere('c.is_weekend = true')
            ->setParameter('start', $weekStart)
            ->setParameter('end', $weekEnd)
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('planning/day.html.twig', [
            'date' => $date,
            'weekDays' => $weekDays,
            'plannings' => $planningRepository->findDayPlanningsWithChildren($date),
        ]);
    }

    #[Route('/{id}', name: 'app_planning_show', methods: ['GET'])]
    public function show(Planning $planning): Response
    {
        return $this->render('planning/show.html.twig', [
            'planning' => $planning,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_planning_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Planning $planning, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlanningForm::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $planning->updateMealStatus();
            $entityManager->flush();

            return $this->redirectToRoute('app_planning_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planning/edit.html.twig', [
            'planning' => $planning,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_planning_delete', methods: ['POST'])]
    public function delete(Request $request, Planning $planning, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$planning->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($planning);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_planning_index', [], Response::HTTP_SEE_OTHER);
    }


}
