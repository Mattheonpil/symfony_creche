<?php

namespace App\Controller;

use App\Repository\ChildRepository;
use App\Repository\PlanningRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
        
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
            'present_children_count' => count($presentChildren),
            'date' => $date,
            'childrenCount' => $planningRepository->countChildrenByDate($date),
            'mealsCount' => $planningRepository->countMealsByDate($date),
            'children' => $planningRepository->findChildrenByDate($date),
        ]);
    }
}
