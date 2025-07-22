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
use App\Form\RecoveryChildForm;
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
use App\Form\AddChildToUserForm;
use App\Repository\UserRepository;

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

        $recoveryChildren = [];
        if ($request->isMethod('GET')) {
            $recoveryChildren[] = new RecoveryChild();
        }

        $form = $this->createForm(InscriptionForm::class, null, [
            'child' => $child,
            'recoveryChildren' => $recoveryChildren,
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
                $userChild = new \App\Entity\UserChild();
                $userChild->setChild($child);
                $userChild->setUser($user);
                $userChild->setRelation($form->get('user')->get('relation')->getData());
                $entityManager->persist($userChild);
                $entityManager->flush();

                // Gérer tous les RecoveryChild soumis
                $recoveryChildren = $form->get('recoveryChildren')->getData();
                foreach ($recoveryChildren as $recoveryChild) {
                    $recovery = $recoveryChild->getRecovery();
                    $entityManager->persist($recovery);
                    $entityManager->flush();
                    $recoveryChild->setChild($child);
                    $recoveryChild->setRecovery($recovery);
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

        $userChildren = $userChildRepository->findBy(['child' => $child]);
        $recoveryChildren = $recoveryChildRepository->findBy(['child' => $child]);

        // Responsables légaux : user principal + RecoveryChild responsables légaux
        $legalRecoveryChildren = array_values(array_filter($recoveryChildren, function($rc) {
            return $rc->isResponsable();
        }));

        // Accompagnateurs autorisés : RecoveryChild non responsables légaux
        $otherRecoveryChildren = array_values(array_filter($recoveryChildren, function($rc) {
            return !$rc->isResponsable();
        }));

        return $this->render('administration/show_inscription.html.twig', [
            'child' => $child,
            'userChildren' => $userChildren,
            'legalRecoveryChildren' => $legalRecoveryChildren,
            'recoveryChildren' => $otherRecoveryChildren,
        ]);
    }

    #[Route('/inscription/edit-user/{id}', name: 'app_inscription_edit_user')]
    public function editUser(
        User $user,
        Request $request, 
        EntityManagerInterface $entityManager,
        UserChildRepository $userChildRepository,
        RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $form = $this->createForm(UserForm::class, $user, [
            'edit_mode' => true
        ]);
        $form->handleRequest($request);

        $userChild = $userChildRepository->findOneBy(['user' => $user]);
        $child = $userChild->getChild();
        $legalRecoveryChildren = $recoveryChildRepository->findBy(['child' => $child, 'is_responsable' => true]);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Informations du responsable mises à jour');
                return $this->redirectToRoute('app_inscription_show', [
                    'childId' => $child->getId()
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        return $this->render('administration/edit/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'child' => $child,
            'legalRecoveryChildren' => $legalRecoveryChildren
        ]);
    }

    #[Route('/inscription/edit-recovery/{id}', name: 'app_inscription_edit_recovery')]
    public function editRecovery(
        Recovery $recovery,
        Request $request, 
        EntityManagerInterface $entityManager,
        RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $recoveryChild = $recoveryChildRepository->findOneBy(['recovery' => $recovery]);
        $form = $this->createForm(RecoveryChildForm::class, $recoveryChild);
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

    #[Route('/inscription/add-responsable/{childId}', name: 'app_responsable_add')]
    public function addResponsable(
        int $childId,
        Request $request,
        EntityManagerInterface $entityManager,
        ChildRepository $childRepository
    ): Response {
        $child = $childRepository->find($childId);
        if (!$child) {
            throw $this->createNotFoundException('Enfant non trouvé');
        }
        $recoveryChild = new \App\Entity\RecoveryChild();
        $recoveryChild->setChild($child);
        $form = $this->createForm(\App\Form\RecoveryChildForm::class, $recoveryChild, [
            'show_is_responsable' => false
        ]);
        $form->handleRequest($request);
        $recoveryChild->setIsResponsable(true);
        if ($form->isSubmitted() && $form->isValid()) {
            $recovery = $recoveryChild->getRecovery();
            $entityManager->persist($recovery);
            $entityManager->flush();
            $recoveryChild->setRecovery($recovery);
            $entityManager->persist($recoveryChild);
            $entityManager->flush();
            $this->addFlash('success', 'Responsable légal ajouté.');
            return $this->redirectToRoute('app_inscription_show', ['childId' => $childId]);
        }
        return $this->render('administration/edit/add_responsable.html.twig', [
            'form' => $form->createView(),
            'child' => $child
        ]);
    }

    #[Route('/inscription/add-accompagnateur/{childId}', name: 'app_accompagnateur_add')]
    public function addAccompagnateur(
        int $childId,
        Request $request,
        EntityManagerInterface $entityManager,
        ChildRepository $childRepository
    ): Response {
        $child = $childRepository->find($childId);
        if (!$child) {
            throw $this->createNotFoundException('Enfant non trouvé');
        }
        $recoveryChild = new \App\Entity\RecoveryChild();
        $recoveryChild->setChild($child);
        $form = $this->createForm(\App\Form\RecoveryChildForm::class, $recoveryChild, [
            'show_is_responsable' => false
        ]);
        $form->handleRequest($request);
        $recoveryChild->setIsResponsable(false);
        if ($form->isSubmitted() && $form->isValid()) {
            $recovery = $recoveryChild->getRecovery();
            $entityManager->persist($recovery);
            $entityManager->flush();
            $recoveryChild->setRecovery($recovery);
            $entityManager->persist($recoveryChild);
            $entityManager->flush();
            $this->addFlash('success', 'Accompagnateur autorisé ajouté.');
            return $this->redirectToRoute('app_inscription_show', ['childId' => $childId]);
        }
        return $this->render('administration/edit/add_accompagnateur.html.twig', [
            'form' => $form->createView(),
            'child' => $child
        ]);
    }

    #[Route('/inscription/edit-recovery-child/{id}', name: 'app_edit_recovery_child', methods: ['POST'])]
    public function editRecoveryChild(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $recoveryChild = $recoveryChildRepository->find($id);
        if (!$recoveryChild) {
            throw $this->createNotFoundException('Responsable légal non trouvé');
        }
        $recovery = $recoveryChild->getRecovery();
        $recovery->setFirstName($request->request->get('first_name'));
        $recovery->setName($request->request->get('name'));
        $recovery->setEmail($request->request->get('email'));
        $recovery->setPhone($request->request->get('phone'));
        $recoveryChild->setRelation($request->request->get('relation'));
        $entityManager->flush();
        $this->addFlash('success', 'Responsable légal modifié.');
        return $this->redirectToRoute('app_inscription_show', ['childId' => $recoveryChild->getChild()->getId()]);
    }

    #[Route('/inscription/delete-recovery-child/{id}', name: 'app_delete_recovery_child', methods: ['POST'])]
    public function deleteRecoveryChild(
        int $id,
        EntityManagerInterface $entityManager,
        RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $recoveryChild = $recoveryChildRepository->find($id);
        if (!$recoveryChild) {
            throw $this->createNotFoundException('Responsable légal non trouvé');
        }
        $childId = $recoveryChild->getChild()->getId();
        $entityManager->remove($recoveryChild);
        $entityManager->flush();
        $this->addFlash('success', 'Responsable légal supprimé.');
        return $this->redirectToRoute('app_inscription_show', ['childId' => $childId]);
    }

    #[Route('/inscription/edit-accompagnateurs/{childId}', name: 'app_edit_accompagnateurs')]
    public function editAccompagnateurs(
        int $childId,
        RecoveryChildRepository $recoveryChildRepository,
        ChildRepository $childRepository
    ): Response {
        $child = $childRepository->find($childId);
        if (!$child) {
            throw $this->createNotFoundException('Enfant non trouvé');
        }
        $accompagnateurs = $recoveryChildRepository->findBy(['child' => $child, 'is_responsable' => false]);
        return $this->render('administration/edit/edit_accompagnateur.html.twig', [
            'child' => $child,
            'accompagnateurs' => $accompagnateurs
        ]);
    }

    #[Route('/ajouter-enfant-utilisateur', name: 'app_add_child_to_user')]
    public function addChildToUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserChildRepository $userChildRepository,
        \App\Repository\RecoveryChildRepository $recoveryChildRepository
    ): Response {
        $formData = [];
        $user = null;
        $relation = null;
        $legalRecoveryChildren = [];
        $simpleRecoveryChildren = [];
        $userChildren = [];
        $mainUserChild = null;
        // Récupérer l'id du user depuis GET (changement user) ou POST (soumission finale)
        $userId = $request->query->get('user') ?? ($request->request->get('add_child_to_user')['user'] ?? null);
        if ($userId) {
            $user = $entityManager->getRepository(\App\Entity\User::class)->find($userId);
            if ($user) {
                $formData['user'] = $user; // pré-remplit le select
                // Récupérer tous les UserChild pour la fiche parent
                $userChildren = $userChildRepository->findBy(['user' => $user], ['id' => 'ASC']);
                // Prendre le premier UserChild pour la logique de relation et de récupération des responsables/accompagnateurs
                $mainUserChild = $userChildren[0] ?? null;
                if ($mainUserChild) {
                    $firstChild = $mainUserChild->getChild();
                    $legalRecoveryChildren = $recoveryChildRepository->findBy(['child' => $firstChild, 'is_responsable' => true]);
                    $simpleRecoveryChildren = $recoveryChildRepository->findBy(['child' => $firstChild, 'is_responsable' => false]);
                }
            }
        }

        // Récupérer les IDs à exclure (depuis le champ hidden)
        $excludedRaw = $request->request->get('excluded_responsables', '');
        if (is_string($excludedRaw) && $excludedRaw !== '') {
            $excludedIds = array_filter(explode(',', $excludedRaw), function($v) { return is_numeric($v) && $v !== ''; });
        } else {
            $excludedIds = [];
        }

        $form = $this->createForm(AddChildToUserForm::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $data['user'];
            $child = $data['child'];
            // Récupération robuste de l'entité User (et Child) depuis la base si besoin
            if ($user instanceof \App\Entity\User) {
                $userId = $user->getId();
            } else {
                $userId = $user;
            }
            $user = $userId ? $entityManager->getRepository(\App\Entity\User::class)->find($userId) : null;

            if (!$child instanceof \App\Entity\Child) {
                $child = $child ? $entityManager->getRepository(\App\Entity\Child::class)->find($child) : null;
            }

            // Copier la relation du mainUserChild
            $relation = $mainUserChild ? $mainUserChild->getRelation() : null;

            // S'assurer que $user et $child sont bien des entités persistées
            // if ($user && !$user instanceof \App\Entity\User) { // This block is now redundant due to the new_code
            //     $user = $entityManager->getRepository(\App\Entity\User::class)->find($user);
            // }
            // if ($child && !$child instanceof \App\Entity\Child) { // This block is now redundant due to the new_code
            //     $child = $entityManager->getRepository(\App\Entity\Child::class)->find($child);
            // }

            // Persister le child AVANT tout persist sur UserChild ou RecoveryChild
            $entityManager->persist($child);

            $userChild = new \App\Entity\UserChild();
            $userChild->setUser($user);
            $userChild->setChild($child);
            $userChild->setRelation($relation);

            $entityManager->persist($userChild);

            // Création du planning pour l'enfant (déjà présent)
            $childForm = $form->get('child');
            $dateDebut = $childForm->get('date_debut')->getData();
            $dateFin = $childForm->get('date_fin')->getData();

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

            // Associer les responsables légaux du premier enfant (sauf exclus)
            if ($mainUserChild && $mainUserChild->getChild()) {
                $firstChild = $mainUserChild->getChild();
                $legalRecoveryChildren = $recoveryChildRepository->findBy(['child' => $firstChild, 'is_responsable' => true]);
                foreach ($legalRecoveryChildren as $rc) {
                    if (in_array($rc->getId(), $excludedIds)) {
                        continue; // On saute ceux à exclure
                    }
                    $newRecoveryChild = new \App\Entity\RecoveryChild();
                    $newRecoveryChild->setChild($child); // $child = nouvel enfant
                    $newRecoveryChild->setRecovery($rc->getRecovery());
                    $newRecoveryChild->setRelation($rc->getRelation());
                    $newRecoveryChild->setIsResponsable(true);
                    $entityManager->persist($newRecoveryChild);
                }

                // Associer les accompagnateurs autorisés du premier enfant (sauf exclus)
                $simpleRecoveryChildren = $recoveryChildRepository->findBy(['child' => $firstChild, 'is_responsable' => false]);
                foreach ($simpleRecoveryChildren as $rc) {
                    if (in_array($rc->getId(), $excludedIds)) {
                        continue;
                    }
                    $newRecoveryChild = new \App\Entity\RecoveryChild();
                    $newRecoveryChild->setChild($child);
                    $newRecoveryChild->setRecovery($rc->getRecovery());
                    $newRecoveryChild->setRelation($rc->getRelation());
                    $newRecoveryChild->setIsResponsable(false);
                    $entityManager->persist($newRecoveryChild);
                }
            }

            $entityManager->flush();

            // Rafraîchir la liste des enfants de l'utilisateur après ajout
            $userChildren = $userChildRepository->findBy(['user' => $user], ['id' => 'ASC']);

            $this->addFlash('success', "Enfant ajouté à l'utilisateur et planning créé !");
            if ($child && $child->getId()) {
                return $this->redirectToRoute('app_child_profile', ['id' => $child->getId()]);
            } elseif ($user && $user->getId()) {
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            } else {
                $this->addFlash('error', 'Utilisateur ou enfant introuvable après la création.');
                return $this->redirectToRoute('app_user_index');
            }
        }

        return $this->render('administration/edit/add_child_to_user.html.twig', [
            'form' => $form->createView(),
            'legalRecoveryChildren' => $legalRecoveryChildren,
            'simpleRecoveryChildren' => $simpleRecoveryChildren,
            'user' => $user,
            'userChildren' => $userChildren,
            'mainUserChild' => $mainUserChild,
        ]);
    }

    #[Route('/ajax/parent-info/{userId}', name: 'ajax_parent_info', methods: ['GET'])]
    public function ajaxParentInfo(
        int $userId,
        UserRepository $userRepository,
        UserChildRepository $userChildRepository
    ): Response {
        $user = $userRepository->find($userId);
        if (!$user) {
            return new Response('<div class="error">Parent non trouvé</div>', 404);
        }
        $userChildren = $userChildRepository->findBy(['user' => $user]);
        return $this->render('administration/edit/_parent_info_fragment.html.twig', [
            'user' => $user,
            'userChildren' => $userChildren,
        ]);
    }

    #[Route('/add-responsable-to-child/{recoveryChildId}', name: 'app_add_responsable_to_child', methods: ['POST'])]
    public function addResponsableToChild(
        int $recoveryChildId,
        Request $request,
        EntityManagerInterface $entityManager,
        \App\Repository\RecoveryChildRepository $recoveryChildRepository,
        \App\Repository\ChildRepository $childRepository
    ): Response {
        $childId = $request->request->get('child_id');
        $child = $childRepository->find($childId);
        $existingRecoveryChild = $recoveryChildRepository->find($recoveryChildId);
        if ($child && $existingRecoveryChild) {
            $newRecoveryChild = new \App\Entity\RecoveryChild();
            $newRecoveryChild->setChild($child);
            $newRecoveryChild->setRecovery($existingRecoveryChild->getRecovery());
            $newRecoveryChild->setRelation($existingRecoveryChild->getRelation());
            $newRecoveryChild->setIsResponsable(true);
            $entityManager->persist($newRecoveryChild);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_add_child_to_user');
    }

    #[Route('/add-accompagnateur-to-child/{recoveryChildId}', name: 'app_add_accompagnateur_to_child', methods: ['POST'])]
    public function addAccompagnateurToChild(
        int $recoveryChildId,
        Request $request,
        EntityManagerInterface $entityManager,
        \App\Repository\RecoveryChildRepository $recoveryChildRepository,
        \App\Repository\ChildRepository $childRepository
    ): Response {
        $childId = $request->request->get('child_id');
        $child = $childRepository->find($childId);
        $existingRecoveryChild = $recoveryChildRepository->find($recoveryChildId);
        if ($child && $existingRecoveryChild) {
            $newRecoveryChild = new \App\Entity\RecoveryChild();
            $newRecoveryChild->setChild($child);
            $newRecoveryChild->setRecovery($existingRecoveryChild->getRecovery());
            $newRecoveryChild->setRelation($existingRecoveryChild->getRelation());
            $newRecoveryChild->setIsResponsable(false);
            $entityManager->persist($newRecoveryChild);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_add_child_to_user');
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
