<?php

namespace App\Controller;

use App\Entity\Requirement;
use App\Form\RequirementsType;
use App\Repository\RequirementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale<%app.supported_locales%>}/crud/requirement')]
class CrudRequirementController extends AbstractController
{
    #[Route(name: 'app_crud_requirement_index', methods: ['GET'])]
    public function index(RequirementRepository $requirementRepository): Response
    {
        return $this->render('crud_requirement/index.html.twig', [
            'requirements' => $requirementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_crud_requirement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $requirement = new Requirement();
        $form = $this->createForm(RequirementsType::class, $requirement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($requirement);
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_requirement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('crud_requirement/new.html.twig', [
            'requirement' => $requirement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_crud_requirement_show', methods: ['GET'])]
    public function show(Requirement $requirement): Response
    {
        return $this->render('crud_requirement/show.html.twig', [
            'requirement' => $requirement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_crud_requirement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Requirement $requirement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RequirementsType::class, $requirement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_requirement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('crud_requirement/edit.html.twig', [
            'requirement' => $requirement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_crud_requirement_delete', methods: ['POST'])]
    public function delete(Request $request, Requirement $requirement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$requirement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($requirement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_crud_requirement_index', [], Response::HTTP_SEE_OTHER);
    }
}
