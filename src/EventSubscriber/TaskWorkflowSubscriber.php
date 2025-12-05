<?php

namespace App\EventSubscriber;

use App\Entity\Task;
use App\Service\TaskMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\WorkflowEvents;

class TaskWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(private TaskMailer $taskMailer) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.task_workflow.completed.start' => 'onTaskStarted',
            'workflow.task_workflow.completed.finish' => 'onTaskFinished',
        ];
    }

    public function onTaskStarted(TransitionEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $this->taskMailer->sendTaskStarted($task);
    }

    public function onTaskFinished(TransitionEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $this->taskMailer->sendTaskFinished($task);
    }
}