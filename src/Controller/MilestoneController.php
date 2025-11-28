<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MilestoneController extends AbstractController
{
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;

    }

    #[Route('/project/{slug}/milestone/{id}', name: 'app_milestone')]
    public function index(string $slug, string $id): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);
        $milestone = $this->projectRepository->findMilestoneById($id);

        return $this->render('milestone/index.html.twig', [
            'controller_name' => 'MilestoneController',
        ]);
    }
}
