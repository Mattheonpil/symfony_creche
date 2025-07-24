<?php

namespace App\Controller;

use App\Repository\ChildRepository;
use App\Repository\PlanningRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Security\Http\Attribute\IsGranted;

// #[IsGranted('ROLE_ADMIN')]
final class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'app_administration')]
    public function index(Request $request, PlanningRepository $planningRepository): Response
    {
        // Définir explicitement le fuseau horaire pour PHP
        date_default_timezone_set('Europe/Paris');
        
        $dateString = $request->query->get('date');
        $date = $dateString ? new \DateTime($dateString) : new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        // Récupérer les enfants présents (absence = false)
        $presentChildren = $planningRepository->findByDate($date);

        // Générer les availabilities pour la barre de disponibilités
        $availabilities = [];
        $max = 20;
        $start = new \DateTimeImmutable($date->format('Y-m-d') . ' 07:00');
        $end = new \DateTimeImmutable($date->format('Y-m-d') . ' 19:00');
        $interval = new \DateInterval('PT15M');
        for ($time = $start; $time < $end; $time = $time->add($interval)) {
            $quarter = $time->format('H:i');
            $count = $planningRepository->countChildrenForQuarter($date->format('Y-m-d'), $quarter);
            $availabilities[$quarter] = $max - $count;
        }

        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
            'present_children_count' => count($presentChildren),
            'date' => $date,
            'childrenCount' => $planningRepository->countChildrenByDate($date),
            'mealsCount' => $planningRepository->countMealsByDate($date),
            'children' => $planningRepository->findChildrenByDate($date),
            'availabilities' => $availabilities,
            'max' => $max,
        ]);
    }
}
