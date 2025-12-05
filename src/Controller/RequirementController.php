<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\RequirementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RequirementController extends AbstractController
{
    private ProjectRepository $projectRepository;
    private RequirementRepository $requirementRepository;

    // Inject repositories for projects and requirements
    public function __construct(
        ProjectRepository $projectRepository,
        RequirementRepository $requirementRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->requirementRepository = $requirementRepository;
    }

    // Route to view a specific requirement within a project
    #[Route('/{_locale<%app.supported_locales%>}/project/{slug}/requirement/{id}', name: 'app_requirement')]
    public function index(string $slug, string $id): Response
    {
        // Find the project by slug
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        // Throw 404 if project not found
        if (!$project) {
            throw $this->createNotFoundException('This project does not exist');
        }

        // Find the requirement by id
        $requirement = $this->requirementRepository->find($id);

        // Throw 404 if requirement not found
        if (!$requirement) {
            throw $this->createNotFoundException('This requirement does not exist');
        }

        // Check that the requirement belongs to the project
        if ($requirement->getProject() !== $project) {
            throw $this->createNotFoundException('This requirement does not belong to this project');
        }

        // Render the requirement template and pass project and requirement
        return $this->render('requirement/requirement.html.twig', [
            'project' => $project,
            'requirement' => $requirement,
        ]);
    }
}
