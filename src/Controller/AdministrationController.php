<?php

namespace App\Controller;

use App\Repository\ChildRepository;
use App\Repository\PlanningRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'app_administration')]
    public function index(
        ChildRepository $childRepository,
        PlanningRepository $planningRepository
        
    ): Response
        // Récupérer la date du jour
    {
        $today = new \DateTime();


        // Nombre d'enfants présents aujourd'hui
        $presentChildren  = $planningRepository->findByDate($today);
    {   
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
            'present_children_count' => count($presentChildren),
            'date' => $today
        ]);
    }
    }
}
