<?php

namespace App\Controller;

use App\Entity\Requirement;
use App\Form\RequirementsType;
use App\Repository\RequirementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Base route for this controller with locale support
#[Route('/{_locale<%app.supported_locales%>}/crud/requirement')]
// Only users with ROLE_USER can access this controller
#[IsGranted('ROLE_USER')]
class CrudRequirementController extends AbstractController
{
    // Route to list all requirements
    #[Route(name: 'app_crud_requirement_index', methods: ['GET'])]
    public function index(RequirementRepository $requirementRepository): Response
    {
        // Render index template and pass all requirements
        return $this->render('crud_requirement/index.html.twig', [
            'requirements' => $requirementRepository->findAll(),
        ]);
    }

    // Route to create a new requirement
    #[Route('/new', name: 'app_crud_requirement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $requirement = new Requirement(); // Create a new Requirement entity
        $form = $this->createForm(RequirementsType::class, $requirement); // Create form
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($requirement); // Save requirement
            $entityManager->flush();

            // Redirect to requirement index after creation
            return $this->redirectToRoute('app_crud_requirement_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the new requirement form template
        return $this->render('crud_requirement/new.html.twig', [
            'requirement' => $requirement,
            'form' => $form,
        ]);
    }

    // Route to show a specific requirement
    #[Route('/{id}', name: 'app_crud_requirement_show', methods: ['GET'])]
    public function show(Requirement $requirement): Response
    {
        // Render show template and pass the requirement
        return $this->render('crud_requirement/show.html.twig', [
            'requirement' => $requirement,
        ]);
    }

    // Route to edit an existing requirement
    #[Route('/{id}/edit', name: 'app_crud_requirement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Requirement $requirement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RequirementsType::class, $requirement); // Create form pre-filled with requirement data
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Save changes to the database

            // Redirect to requirement index after editing
            return $this->redirectToRoute('app_crud_requirement_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render edit form template
        return $this->render('crud_requirement/edit.html.twig', [
            'requirement' => $requirement,
            'form' => $form,
        ]);
    }

    // Route to delete a requirement
    #[Route('/{id}', name: 'app_crud_requirement_delete', methods: ['POST'])]
    public function delete(Request $request, Requirement $requirement, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete'.$requirement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($requirement); // Remove requirement from database
            $entityManager->flush();
        }

        // Redirect to requirement index after deletion
        return $this->redirectToRoute('app_crud_requirement_index', [], Response::HTTP_SEE_OTHER);
    }
}
