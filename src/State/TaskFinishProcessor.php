<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TaskMailer;
use Symfony\Component\Workflow\WorkflowInterface;

class TaskFinishProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskMailer $taskMailer,
        private WorkflowInterface $taskWorkflow  // Inject workflow service
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Task
    {
        if (!$data instanceof Task) {
            throw new \InvalidArgumentException('Expected Task entity');
        }

        // Apply workflow transition
        if ($this->taskWorkflow->can($data, 'finish')) {
            $this->taskWorkflow->apply($data, 'finish');  // Triggers any workflow events
        }

        $this->entityManager->flush();

        // Send email
        $this->taskMailer->sendTaskFinished($data);

        return $data;
    }
}
