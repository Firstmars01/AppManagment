<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Task;
use App\Repository\TaskTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class TaskStartProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskTypeRepository $taskTypeRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Task
    {
        if (!$data instanceof Task) {
            throw new \InvalidArgumentException('Expected Task entity');
        }

        // Set actual start date to now
        $data->setActualStartDate(new DateTimeImmutable());

        // Update task type to "Started but not finished"
        $startedType = $this->taskTypeRepository->findOneBy(['label' => 'Started but not finished']);
        if ($startedType) {
            $data->setTaskType($startedType);
        }

        $this->entityManager->flush();

        return $data;
    }
}