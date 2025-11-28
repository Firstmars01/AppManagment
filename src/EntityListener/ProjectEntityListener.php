<?php

namespace App\EntityListener;

use App\Entity\Project;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProjectEntityListener
{

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Project $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }

    public function preUpdate(Project $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }

}