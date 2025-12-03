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
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/crud/project')]
class CrudProjectController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger
    ) {}

    #[Route(name: 'app_crud_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('crud_project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_crud_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Génération du slug
            $project->computeSlug($this->slugger);

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_project_index');
        }

        return $this->render('crud_project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_crud_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('crud_project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_crud_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Regénère le slug si le nom change
            $project->computeSlug($this->slugger);

            $entityManager->flush();

            return $this->redirectToRoute('app_crud_project_index');
        }

        return $this->render('crud_project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_crud_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_crud_project_index');
    }
}
