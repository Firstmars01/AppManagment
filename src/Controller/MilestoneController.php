<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\MilestoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MilestoneController extends AbstractController
{
    private ProjectRepository $projectRepository;
    private MilestoneRepository $milestoneRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        MilestoneRepository $milestoneRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->milestoneRepository = $milestoneRepository;
    }

    #[Route('/project/{slug}/milestone/{id}', name: 'app_milestone')]
    public function index(string $slug, string $id): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('Ce projet n\'existe pas');
        }

        $milestone = $this->milestoneRepository->find($id);

        if (!$milestone) {
            throw $this->createNotFoundException('Ce milestone n\'existe pas');
        }

        // Vérifier que le milestone appartient bien au projet
        if ($milestone->getProject() !== $project) {
            throw $this->createNotFoundException('Ce milestone n\'appartient pas à ce projet');
        }

        return $this->render('milestone/milestone.html.twig', [
            'project' => $project,
            'milestone' => $milestone,
        ]);
    }
}