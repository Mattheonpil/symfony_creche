<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Form\PlanningForm;
use App\Form\PlanningPresenceForm;
use App\Repository\PlanningRepository;
use App\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/{id}/presence-form', name: 'app_planning_presence_form', methods: ['GET'])]
    public function presenceForm(Planning $planning): Response
    {
        $form = $this->createForm(PlanningPresenceForm::class, $planning, ['csrf_protection' => false]);
        return $this->render('planning/_presence_form.html.twig', [
            'planning' => $planning,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/presence-save', name: 'app_planning_presence_save', methods: ['POST'])]
    public function presenceSave(Request $request, Planning $planning, EntityManagerInterface $em): JsonResponse
    {
        $form = $this->createForm(PlanningPresenceForm::class, $planning, ['csrf_protection' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $em->refresh($planning);

            // Logique couleur identique aux templates
            $barColor = 'primary';
            $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
            $planningDate = $planning->getDate();
            $planningStart = $planning->getStartTime();
            $planningDateTime = null;
            if ($planningDate && $planningStart) {
                $planningDateTime = (clone $planningDate)->setTime((int)$planningStart->format('H'), (int)$planningStart->format('i'));
            }
            if ($planning->isAbsence()) {
                $barColor = 'red';
            } elseif ($planningDate && $planningDate > $now->setTime(0,0)) {
                $barColor = 'primary';
            } elseif ($planningDate && $planningDate < $now->setTime(0,0)) {
                if (!$planning->getActualArrival()) {
                    $barColor = 'orange';
                } else {
                    $barColor = 'green';
                }
            } else {
                // Jour courant
                if ($planningDateTime && $now < $planningDateTime) {
                    $barColor = 'primary';
                } elseif (!$planning->getActualArrival()) {
                    $barColor = 'orange';
                } else {
                    $barColor = 'green';
                }
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Présence enregistrée !',
                'absence' => $planning->isAbsence(),
                'barColor' => $barColor,
            ]);
        }

        // Si erreur, renvoyer le formulaire HTML pour affichage des erreurs
        return new JsonResponse([
            'success' => false,
            'form' => $this->renderView('planning/_presence_form.html.twig', [
                'planning' => $planning,
                'form' => $form->createView(),
            ]),
        ]);
    }

    #[Route('/child/{id}', name: 'app_planning_child', methods: ['GET'])]
    public function planningChild(
        \App\Entity\Child $child,
        Request $request,
        CalendarRepository $calendarRepository,
        PlanningRepository $planningRepository
    ): Response {
        // Récupération du mois/année depuis la query string, sinon mois courant
        $month = (int) $request->query->get('month', date('m'));
        $year = (int) $request->query->get('year', date('Y'));

        // Premier et dernier jour du mois
        $start = new \DateTimeImmutable("$year-$month-01");
        $end = $start->modify('last day of this month');

        // Récupérer tous les jours ouvrables du mois (Calendar)
        $calendars = $calendarRepository->createQueryBuilder('c')
            ->where('c.date >= :start')
            ->andWhere('c.date <= :end')
            ->andWhere('c.is_weekend = 1')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();

        // Récupérer tous les plannings de l'enfant pour ce mois
        $plannings = $planningRepository->createQueryBuilder('p')
            ->andWhere('p.child = :child')
            ->andWhere('p.date >= :start')
            ->andWhere('p.date <= :end')
            ->setParameter('child', $child)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        // Indexer les plannings par date (Y-m-d)
        $planningsByDate = [];
        foreach ($plannings as $planning) {
            $planningsByDate[$planning->getDate()->format('Y-m-d')] = $planning;
        }

        // Calcul des mois précédent/suivant
        $prevMonth = (clone $start)->modify('-1 month');
        $nextMonth = (clone $start)->modify('+1 month');

        return $this->render('planning/planning_child.html.twig', [
            'child' => $child,
            'calendars' => $calendars,
            'planningsByDate' => $planningsByDate,
            'month' => $month,
            'year' => $year,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    #[Route('/{id}/row', name: 'app_planning_row', methods: ['GET'])]
    public function planningRow(Planning $planning): Response
    {
        return $this->render('planning/_planning_row.html.twig', [
            'planning' => $planning,
        ]);
    }
}
