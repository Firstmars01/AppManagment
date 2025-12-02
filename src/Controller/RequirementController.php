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

    public function __construct(
        ProjectRepository $projectRepository,
        RequirementRepository $requirementRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->requirementRepository = $requirementRepository;
    }

    #[Route('/project/{slug}/requirement/{id}', name: 'app_requirement')]
    public function index(string $slug, string $id): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('Ce projet n\'existe pas');
        }

        $requirement = $this->requirementRepository->find($id);

        if (!$requirement) {
            throw $this->createNotFoundException('Ce requirement n\'existe pas');
        }

        if ($requirement->getProject() !== $project) {
            throw $this->createNotFoundException('Ce requirement n\'appartient pas à ce projet');
        }

        return $this->render('requirement/requirement.html.twig', [
            'project' => $project,
            'requirement' => $requirement,
        ]);
    }
}