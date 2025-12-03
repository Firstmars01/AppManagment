<?php

namespace App\Form;

use App\Entity\Milestone;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MilestoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('plannedStartDate', null, [
                'widget' => 'single_text',
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
            ])
            ->add('manager', EntityType::class, [
                'class' => user::class,
                'choice_label' => function (User $user) {
                    return $user->getName() . ' ' . $user->getSecondName();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Milestone::class,
        ]);
    }
}
