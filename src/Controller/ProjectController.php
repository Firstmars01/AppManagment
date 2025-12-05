<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    private ProjectRepository $projectRepository;

    // Inject ProjectRepository for database access
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    // Redirect the homepage without locale to the default 'en' locale
    #[Route('/', name: 'homepage', requirements: ['_locale' => 'en|fr'])]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('project_list', ['_locale' => 'en']);
    }

    // List all projects with locale support
    #[Route('/{_locale<%app.supported_locales%>}/', name: 'project_list')]
    public function index(): Response
    {
        // Get all projects sorted by creation date descending
        $projects = $this->projectRepository->findBy([], ['createdAt' => 'DESC']);

        // Render the project list template
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    // Show details of a single project by slug
    #[Route('/{_locale<%app.supported_locales%>}/project/{slug}', name: 'project_show')]
    public function show(string $slug): Response
    {
        // Find the project by slug
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        // Throw 404 if project not found
        if (!$project) {
            throw $this->createNotFoundException('This project does not exist');
        }

        // Render the project details template
        return $this->render('project/projectdetails.html.twig', [
            'project' => $project,
        ]);
    }
}
