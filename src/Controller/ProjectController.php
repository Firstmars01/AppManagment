<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ProjectController extends AbstractController
{
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    #[Route('/', name: 'project_list')]
    public function index(): Response
    {
        $projects = $this->projectRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/{id}', name: 'project_show')]
    public function show(string $id): Response
    {
        $project = $this->projectRepository->find(Uuid::fromString($id));

        if (!$project) {
            throw $this->createNotFoundException('Le projet n\'existe pas');
        }

        return $this->render('project/projectdetails.html.twig', [
            'project' => $project,
        ]);
    }
}
