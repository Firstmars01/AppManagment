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
            ->add('isFunctional', null, [
                'required' => false,
            ])
            ->add('plannedStartDate', null, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
            ])
            ->add('daysEstimate', null, [
                'required' => true,
            ])
            ->add('description', null, [
                'required' => true,
            ])
            ->add('milestone', EntityType::class, [
                'class' => Milestone::class,
                'choice_label' => 'label',
                'placeholder' => 'Aucun jalon',
                'required' => true,
            ])
            ->add('manager', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (?User $user) {
                    return $user ? $user->getName() . ' ' . $user->getSecondName() : '';
                },
                'placeholder' => 'Aucun responsable',
                'required' => true,
            ])
            ->add('previousTask', EntityType::class, [
                'class' => Task::class,
                'choice_label' => 'label',
                'placeholder' => 'Aucune tâche précédente',
                'required' => false,
            ])
            ->add('requirements', EntityType::class, [
                'class' => Requirement::class,
                'choice_label' => function (?Requirement $requirement) {
                    return $requirement ? $requirement->getDescription() : '';
                },
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'required' => true,
            ])
            ->add('taskType', EntityType::class, [
                'class' => TaskType::class,
                'choice_label' => 'label',
                'placeholder' => 'Choose a task type',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'allow_extra_fields' => true,
        ]);
    }
}
