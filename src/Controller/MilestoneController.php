<?php

namespace App\Controller;

use App\Entity\Milestone;
use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

class MilestoneController extends AbstractController
{

    #[Route('/project/{slug}/milestone/{id}', name: 'app_milestone')]
    public function index(
        #[MapEntity(mapping: ['slug' => 'slug'])] Project $project,
        #[MapEntity(mapping: ['id' => 'id'])] Milestone $milestone
    ): Response {
        if ($project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce projet');
        }

        if ($milestone->getProject() !== $project) {
            throw $this->createNotFoundException('Ce milestone n\'appartient pas à ce projet');
        }

        return $this->render('milestone/milestone.html.twig', [
            'project' => $project,
            'milestone' => $milestone,
        ]);
    }


}