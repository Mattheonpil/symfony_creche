<?php

namespace App\Controller;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Entity\User;
use App\Entity\Child;
use App\Form\UserForm;
use App\Form\ChildForm;
use App\Entity\Calendar;
use App\Entity\Planning;
use App\Entity\Recovery;
use App\Entity\UserChild;
use App\Form\RecoveryForm;
use App\Entity\RecoveryChild;
use App\Form\InscriptionForm;
use App\Repository\ChildRepository;
use App\Repository\PlanningRepository;
use App\Repository\UserChildRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\InscriptionController;
use App\Repository\RecoveryChildRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $child = new Child();
        $user = new User();

        $form = $this->createForm(InscriptionForm::class, null, [
            'child' => $child,
            'recovery' => null,  // On passe null par défaut
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Gérer l'utilisateur
                $plainPassword = $form->get('user')->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $user->setRoles(['ROLE_USER', 'ROLE_PARENT']);
                $entityManager->persist($user);
                
                // Persister l'enfant
                $entityManager->persist($child);
                
                // Flush pour générer les IDs
                $entityManager->flush();

                // Créer la relation UserChild
                $userChild = new UserChild();
                $userChild->setChild($child);
                $userChild->setUser($user);
                $userChild->setRelation($form->get('user')->get('relation')->getData());
                $entityManager->persist($userChild);
                $entityManager->flush();

                // Vérifier si un recovery a été soumis et n'est pas vide
                $recoveryData = $form->get('recovery')->getData();
                if ($recoveryData && !empty($recoveryData->getName())) {
                    $entityManager->persist($recoveryData);
                    $entityManager->flush();
                    
                    $recoveryChild = new RecoveryChild();
                    $recoveryChild->setChild($child);
                    $recoveryChild->setRecovery($recoveryData);
                    $recoveryChild->setIsResponsable($form->get('recovery')->get('is_legal_guardian')->getData());
                    $recoveryChild->setRelation($form->get('recovery')->get('relation')->getData());
                    $entityManager->persist($recoveryChild);
                    $entityManager->flush();
                }

                // Récupérer les dates et horaires du planning
                $childForm = $form->get('child');
                $dateDebut = $childForm->get('date_debut')->getData();
                $dateFin = $childForm->get('date_fin')->getData();

                // Récupérer les horaires
                $horaires = [
                    'lundi_a' => $childForm->get('lundi_a')->getData(),
                    'lundi_d' => $childForm->get('lundi_d')->getData(),
                    'mardi_a' => $childForm->get('mardi_a')->getData(),
                    'mardi_d' => $childForm->get('mardi_d')->getData(),
                    'mercredi_a' => $childForm->get('mercredi_a')->getData(),
                    'mercredi_d' => $childForm->get('mercredi_d')->getData(),
                    'jeudi_a' => $childForm->get('jeudi_a')->getData(),
                    'jeudi_d' => $childForm->get('jeudi_d')->getData(),
                    'vendredi_a' => $childForm->get('vendredi_a')->getData(),
                    'vendredi_d' => $childForm->get('vendredi_d')->getData(),
                ];

                // Créer les entrées du planning
                $this->createCalendarEntries($child, $horaires, $dateDebut, $dateFin, $entityManager);

                $entityManager->flush();

                $this->addFlash('success', 'Inscription réussie !');
                return $this->redirectToRoute('app_inscription_show', ['childId' => $child->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('administration/inscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/inscription/show/{childId}', name: 'app_inscription_show')]
    public function show(
        int $childId,
        ChildRepository $childRepository,
        UserChildRepository $userChildRepository,
        RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $child = $childRepository->find($childId);
        if (!$child) {
            throw $this->createNotFoundException('Enfant non trouvé');
        }

        $userChild = $userChildRepository->findOneBy(['child' => $child]);
        $recoveryChild = $recoveryChildRepository->findOneBy(['child' => $child]);

        return $this->render('administration/show_inscription.html.twig', [
            'child' => $child,
            'user' => $userChild->getUser(),
            'userChild' => $userChild,
            'recovery' => $recoveryChild ? $recoveryChild->getRecovery() : null,
            'recoveryChild' => $recoveryChild,
        ]);
    }

    #[Route('/inscription/edit-user/{id}', name: 'app_inscription_edit_user')]
    public function editUser(
        User $user,
        Request $request, 
        EntityManagerInterface $entityManager,
        UserChildRepository $userChildRepository
    ): Response {
        $form = $this->createForm(UserForm::class, $user, [
            'edit_mode' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userChild = $userChildRepository->findOneBy(['user' => $user]);
                $entityManager->flush();
                
                $this->addFlash('success', 'Informations du responsable mises à jour');
                return $this->redirectToRoute('app_inscription_show', [
                    'childId' => $userChild->getChild()->getId()
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        return $this->render('administration/edit/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'child' => $userChildRepository->findOneBy(['user' => $user])->getChild()
        ]);
    }

    #[Route('/inscription/edit-recovery/{id}', name: 'app_inscription_edit_recovery')]
    public function editRecovery(
        Recovery $recovery,
        Request $request, 
        EntityManagerInterface $entityManager,
        RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $form = $this->createForm(RecoveryForm::class, $recovery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Informations de l\'accompagnateur mises à jour');
                
                $recoveryChild = $recoveryChildRepository->findOneBy(['recovery' => $recovery]);
                return $this->redirectToRoute('app_inscription_show', [
                    'childId' => $recoveryChild->getChild()->getId()
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        $recoveryChild = $recoveryChildRepository->findOneBy(['recovery' => $recovery]);
        return $this->render('administration/edit/edit_recovery.html.twig', [
            'form' => $form->createView(),
            'recovery' => $recovery,
            'child' => $recoveryChild->getChild()
        ]);
    }

    #[Route('/inscription/edit-child/{id}', name: 'app_inscription_edit_child')]
    public function editChild(
        Child $child,
        Request $request, 
        EntityManagerInterface $entityManager,
        PlanningRepository $planningRepository
    ): Response {
        $form = $this->createForm(ChildForm::class, $child);

        // Récupérer la dernière date du planning
        $lastDate = $planningRepository->findLastPlanningDate($child);
        
        // Préremplir le champ date_fin avec la dernière date
        if ($lastDate) {
            $form->get('date_fin')->setData($lastDate);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Mise à jour des informations de base de l'enfant
                $entityManager->persist($child);
                
                // Récupération des dates et horaires
                $dateDebut = $form->get('date_debut')->getData();
                $dateFin = $form->get('date_fin')->getData();
                
                if ($dateDebut && $dateFin) {
                    // Supprimer les anciens plannings sur la période
                    $planningRepository->deletePlanningBetweenDates($child, $dateDebut, $dateFin);
                    
                    // Récupérer les horaires
                    $horaires = [
                        'lundi_a' => $form->get('lundi_a')->getData(),
                        'lundi_d' => $form->get('lundi_d')->getData(),
                        'mardi_a' => $form->get('mardi_a')->getData(),
                        'mardi_d' => $form->get('mardi_d')->getData(),
                        'mercredi_a' => $form->get('mercredi_a')->getData(),
                        'mercredi_d' => $form->get('mercredi_d')->getData(),
                        'jeudi_a' => $form->get('jeudi_a')->getData(),
                        'jeudi_d' => $form->get('jeudi_d')->getData(),
                        'vendredi_a' => $form->get('vendredi_a')->getData(),
                        'vendredi_d' => $form->get('vendredi_d')->getData(),
                    ];

                    // Créer les nouveaux plannings
                    $this->createCalendarEntries($child, $horaires, $dateDebut, $dateFin, $entityManager);
                }

                $entityManager->flush();
                $this->addFlash('success', 'Informations de l\'enfant et planning mis à jour');
                
                return $this->redirectToRoute('app_inscription_show', ['childId' => $child->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('administration/edit/edit_child.html.twig', [
            'form' => $form->createView(),
            'child' => $child
        ]);
    }

    private function createCalendarEntries(
        Child $child,
        array $horaires,
        \DateTime $dateDebut,
        \DateTime $dateFin,
        EntityManagerInterface $entityManager
    ): void {
        $calendar = $entityManager->getRepository(Calendar::class);
        
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($dateDebut, $interval, $dateFin);

        // Map des jours avec la première lettre en minuscule pour les clés des horaires
        $joursSemaine = [
            'Monday' => ['db' => 'Lundi', 'form' => 'lundi'],
            'Tuesday' => ['db' => 'Mardi', 'form' => 'mardi'],
            'Wednesday' => ['db' => 'Mercredi', 'form' => 'mercredi'],
            'Thursday' => ['db' => 'Jeudi', 'form' => 'jeudi'],
            'Friday' => ['db' => 'Vendredi', 'form' => 'vendredi']
        ];

        foreach ($period as $date) {
            $calendarDay = $calendar->findOneBy(['date' => $date]);
            
            if (!$calendarDay || !$calendarDay->isClosed()) {
                continue;
            }

            $jour = $date->format('l'); // Récupère le jour en anglais
            if (isset($joursSemaine[$jour])) {
                $jourForm = $joursSemaine[$jour]['form'];
                $heureArriveeKey = $jourForm . '_a';
                $heureDepartKey = $jourForm . '_d';

                if (isset($horaires[$heureArriveeKey]) && isset($horaires[$heureDepartKey])) {
                    try {
                        $planning = new Planning();
                        $planning->setChild($child);
                        $planning->setDate($date);
                        $planning->setCalendar($calendarDay);
                        $planning->setStartTime($horaires[$heureArriveeKey]);
                        $planning->setEndTime($horaires[$heureDepartKey]);
                        $planning->setAbsence(false);
                        $planning->setMeal(true);
                        
                        $entityManager->persist($planning);
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de la création du planning : ' . $e->getMessage());
                    }
                }
            }
        }
    }
}
