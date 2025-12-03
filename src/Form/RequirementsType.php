<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Requirement;
use App\Entity\RequirementType;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequirementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('isFunctional')
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'placeholder' => 'Select project',
            ])

            ->add('requirementType', EntityType::class, [
                'class' => RequirementType::class,
                'choice_label' => 'label',
                'placeholder' => 'Select type',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Requirement::class,
        ]);
    }
}
