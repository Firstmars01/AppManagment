<?php

namespace App\Controller;

use App\Entity\Milestone;
use App\Form\MilestoneType;
use App\Repository\MilestoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Base route for this controller, with locale support
#[Route('/{_locale<%app.supported_locales%>}/crud/milestone')]
// Only users with ROLE_USER can access this controller
#[IsGranted('ROLE_USER')]
class CrudMilestoneController extends AbstractController
{
    // Route to list all milestones
    #[Route(name: 'app_crud_milestone_index', methods: ['GET'])]
    public function index(MilestoneRepository $milestoneRepository): Response
    {
        // Render the index template and pass all milestones
        return $this->render('crud_milestone/index.html.twig', [
            'milestones' => $milestoneRepository->findAll(),
        ]);
    }

    // Route to create a new milestone
    #[Route('/new', name: 'app_crud_milestone_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $milestone = new Milestone(); // Create new Milestone entity
        $form = $this->createForm(MilestoneType::class, $milestone); // Create form
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($milestone); // Save milestone
            $entityManager->flush();

            // Redirect to milestone index after creation
            return $this->redirectToRoute('app_crud_milestone_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form template if not submitted or invalid
        return $this->render('crud_milestone/new.html.twig', [
            'milestone' => $milestone,
            'form' => $form,
        ]);
    }

    // Route to show a specific milestone
    #[Route('/{id}', name: 'app_crud_milestone_show', methods: ['GET'])]
    public function show(Milestone $milestone): Response
    {
        // Render the show template and pass the milestone
        return $this->render('crud_milestone/show.html.twig', [
            'milestone' => $milestone,
        ]);
    }

    // Route to edit an existing milestone
    #[Route('/{id}/edit', name: 'app_crud_milestone_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Milestone $milestone, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MilestoneType::class, $milestone); // Create form with existing milestone data
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Save changes to the database

            // Redirect to milestone index after editing
            return $this->redirectToRoute('app_crud_milestone_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the edit form template
        return $this->render('crud_milestone/edit.html.twig', [
            'milestone' => $milestone,
            'form' => $form,
        ]);
    }

    // Route to delete a milestone
    #[Route('/{id}', name: 'app_crud_milestone_delete', methods: ['POST'])]
    public function delete(Request $request, Milestone $milestone, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete'.$milestone->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($milestone); // Remove milestone from database
            $entityManager->flush();
        }

        // Redirect to milestone index after deletion
        return $this->redirectToRoute('app_crud_milestone_index', [], Response::HTTP_SEE_OTHER);
    }
}
