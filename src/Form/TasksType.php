<?php

namespace App\Form;

use App\Entity\Milestone;
use App\Entity\Requirement;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\TaskType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TasksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('isFunctional')
            ->add('plannedStartDate', null, [
                'widget' => 'single_text',
            ])
            ->add('daysEstimate')
            ->add('description')
            ->add('milestone', EntityType::class, [
                'class' => Milestone::class,
                'choice_label' => 'label',
            ])
            ->add('manager', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName() . ' ' . $user->getSecondName();
                },
            ])
            ->add('previousTask', EntityType::class, [
                'class' => Task::class,
                'choice_label' => 'label',
            ])
            ->add('requirements', EntityType::class, [
                'class' => Requirement::class,
                'choice_label' => function(Requirement $requirement) {
                    return $requirement->getDescription();
                },
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('taskType', EntityType::class, [
                'class' => TaskType::class,
                'choice_label' => 'label',
                'placeholder' => 'Choose a task type',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
