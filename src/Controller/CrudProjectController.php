<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

// Base route for this controller with locale support
#[Route('/{_locale<%app.supported_locales%>}/crud/project')]
// Only users with ROLE_USER can access this controller
#[IsGranted('ROLE_USER')]
class CrudProjectController extends AbstractController
{
    // Inject SluggerInterface for slug generation
    public function __construct(
        private SluggerInterface $slugger
    ) {}

    // List all projects
    #[Route(name: 'app_crud_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        // Render index template and pass all projects
        return $this->render('crud_project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }

    // Create a new project
    #[Route('/new', name: 'app_crud_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project(); // Create new Project entity
        $form = $this->createForm(ProjectType::class, $project); // Create form
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {

            // Generate slug for the project
            $project->computeSlug($this->slugger);

            $entityManager->persist($project); // Save project
            $entityManager->flush();

            // Redirect to project index after creation
            return $this->redirectToRoute('app_crud_project_index');
        }

        // Render the new project form template
        return $this->render('crud_project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    // Show details of a project
    #[Route('/{id}', name: 'app_crud_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        // Render show template and pass the project
        return $this->render('crud_project/show.html.twig', [
            'project' => $project,
        ]);
    }

    // Edit an existing project
    #[Route('/{id}/edit', name: 'app_crud_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project); // Create form pre-filled with project data
        $form->handleRequest($request); // Handle form submission

        if ($form->isSubmitted() && $form->isValid()) {

            // Update slug after editing
            $project->computeSlug($this->slugger);

            $entityManager->flush(); // Save changes to the database

            // Redirect to project index after editing
            return $this->redirectToRoute('app_crud_project_index');
        }

        // Render edit form template
        return $this->render('crud_project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    // Delete a project
    #[Route('/{id}', name: 'app_crud_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project); // Remove project from database
            $entityManager->flush();
        }

        // Redirect to project index after deletion
        return $this->redirectToRoute('app_crud_project_index');
    }
}
