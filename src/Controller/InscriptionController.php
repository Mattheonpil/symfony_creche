<?php

namespace App\Controller;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Entity\User;
use App\Entity\Child;
use App\Form\UserForm;
use App\Entity\Calendar;
use App\Form\ChildForm;
use App\Entity\UserChild;
use App\Entity\Recovery;
use App\Form\InscriptionForm;
use App\Entity\Planning;
use App\Form\RecoveryForm;
// use App\Entity\recoverysChilds;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\InscriptionController;
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
        $recovery = new Recovery();
        $user = new User();

        $form = $this->createForm(InscriptionForm::class, null, [
            'child' => $child,
            'recovery' => $recovery,
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formData = $form->getData();
                
                // Persister l'enfant
                $entityManager->persist($child);
                // $entityManager->flush();


                // Persister le recovery
                $entityManager->persist($recovery);

                    // Créer la relation
                $recovery->addChild($child);
                $child->addRecovery($recovery);
    
                $entityManager->flush();

                // Créer la relation recoverysChilds
                // $recoverysChilds = new recoverysChilds();
                // $recoverysChilds->setChild($child);
                // $recoverysChilds->setrecovery($recovery);
                // $recoverysChilds->setLien($form->get('recovery')->get('lien')->getData());
                // $entityManager->persist($recoverysChilds);

                // Gérer l'utilisateur
                $plainPassword = $form->get('user')->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $user->setRoles(['ROLE_PARENT']);
                $entityManager->persist($user);
                $entityManager->flush();

                // Créer la relation UsersChilds
                $userChild = new UserChild();
                $userChild->setChild($child);
                $userChild->setUser($user);
                $userChild->setRelation($form->get('user')->get('relation')->getData());
                $entityManager->persist($userChild);
                $entityManager->flush();

                $childForm = $form->get('child');
            
                // Récupérer les dates
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

                $this->createCalendarEntries($child, $horaires, $dateDebut, $dateFin, $entityManager);

                $entityManager->flush();

                $this->addFlash('success', 'Inscription réussie !');
                return $this->redirectToRoute('app_child_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('administration/inscription.html.twig', [
            'form' => $form,
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
                    $planning->setAbsence(true);
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
