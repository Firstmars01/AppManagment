<?php

namespace App\Service;

use App\Entity\Task;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TaskMailer
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendTaskStarted(Task $task): void
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($task->getManager()->getEmail())
            ->subject('Task Started')
            ->text(sprintf('The task "%s" has been started.', $task->getLabel()));

        $this->mailer->send($email);
    }

    public function sendTaskFinished(Task $task): void
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($task->getManager()->getEmail())
            ->subject('Task Finished')
            ->text(sprintf('The task "%s" has been finished.', $task->getLabel()));

        $this->mailer->send($email);
    }
}
