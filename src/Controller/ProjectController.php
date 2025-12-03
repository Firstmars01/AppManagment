<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractController
{
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }


    #[Route('/', name: 'homepage', requirements: ['_locale' => 'en|fr'])]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('project_list', ['_locale' => 'en']);
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'project_list')]
    public function index(): Response
    {
        $projects = $this->projectRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/{slug}', name: 'project_show')]
    public function show(string $slug): Response
    {
        $project = $this->projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('This project does not exist');
        }

        return $this->render('project/projectdetails.html.twig', [
            'project' => $project,
        ]);
    }

}
