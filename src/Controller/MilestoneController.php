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
    // Route to view a specific milestone within a project
    #[Route('/{_locale<%app.supported_locales%>}/project/{slug}/milestone/{id}', name: 'app_milestone')]
    public function index(
        // Automatically map the 'slug' route parameter to a Project entity
        #[MapEntity(mapping: ['slug' => 'slug'])] Project $project,
        // Automatically map the 'id' route parameter to a Milestone entity
        #[MapEntity(mapping: ['id' => 'id'])] Milestone $milestone
    ): Response {

        // Check that the milestone belongs to the project
        if ($milestone->getProject() !== $project) {
            throw $this->createNotFoundException('Milestone not found in this project.');
        }

        // Render the milestone template and pass project and milestone data
        return $this->render('milestone/milestone.html.twig', [
            'project' => $project,
            'milestone' => $milestone,
        ]);
    }
}
