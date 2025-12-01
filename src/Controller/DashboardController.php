<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        // Si ton entity Project a une propriété owner (ManyToOne(User))
        $projects = $this->projectRepository->findBy(
            ['owner' => $user],
            ['id' => 'ASC'] // ou un autre tri
        );

        return $this->render('dashboard/dashboard.html.twig', [
            'projects' => $projects,
        ]);
    }
}
