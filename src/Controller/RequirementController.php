<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\MilestoneRepository;
use App\Repository\RequirementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RequirementController extends AbstractController
{
    private ProjectRepository $projectRepository;
    private MilestoneRepository $milestoneRepository;
    private RequirementRepository $requirementRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        MilestoneRepository $milestoneRepository,
        RequirementRepository $requirementRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->milestoneRepository = $milestoneRepository;
        $this->requirementRepository = $requirementRepository;
    }

    #[Route('/project/{slug}/milestone/{milestoneId}/requirement/{id}', name: 'app_requirement')]
    public function index(string $slug, int $milestoneId, int $id): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('Ce projet n\'existe pas');
        }

        $milestone = $this->milestoneRepository->find($milestoneId);

        if (!$milestone || $milestone->getProject() !== $project) {
            throw $this->createNotFoundException('Ce milestone n\'existe pas');
        }

        $requirement = $this->requirementRepository->find($id);

        if (!$requirement) {
            throw $this->createNotFoundException('Ce requirement n\'existe pas');
        }

        // Vérifier que le requirement appartient bien au milestone
        if ($requirement->getMilestone() !== $milestone) {
            throw $this->createNotFoundException('Ce requirement n\'appartient pas à ce milestone');
        }

        return $this->render('requirement/index.html.twig', [
            'project' => $project,
            'milestone' => $milestone,
            'requirement' => $requirement,
        ]);
    }
}