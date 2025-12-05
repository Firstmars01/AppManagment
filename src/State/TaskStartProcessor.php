<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TaskMailer;
use Symfony\Component\Workflow\WorkflowInterface;
use DateTimeImmutable;

class TaskStartProcessor implements ProcessorInterface
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

        // Set actual start date
        $data->setActualStartDate(new DateTimeImmutable());

        // Apply workflow transition
        if ($this->taskWorkflow->can($data, 'start')) {
            $this->taskWorkflow->apply($data, 'start');  // Triggers any workflow events
        }

        $this->entityManager->flush();

        // Send email
        $this->taskMailer->sendTaskStarted($data);

        return $data;
    }
}
