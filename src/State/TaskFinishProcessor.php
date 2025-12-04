<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Task;
use App\Repository\TaskTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskFinishProcessor implements ProcessorInterface
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

        // Update task type to "Finished"
        $finishedType = $this->taskTypeRepository->findOneBy(['label' => 'Finished']);
        if ($finishedType) {
            $data->setTaskType($finishedType);
        }

        $this->entityManager->flush();

        return $data;
    }
}