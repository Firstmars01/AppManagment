<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Requirement;
use App\Entity\RequirementType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequirementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champs de base
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
                'required' => false,
            ]);

        // Fonction pour ajouter ou retirer requirementType selon isFunctional
        $formModifier = function ($form, ?bool $isFunctional) {
            if ($isFunctional) {
                $form->remove('requirementType');
            } else {
                // Ajoute le champ si absent
                if (!$form->has('requirementType')) {
                    $form->add('requirementType', EntityType::class, [
                        'class' => RequirementType::class,
                        'choice_label' => 'label',
                        'placeholder' => 'Select type',
                        'required' => false,
                    ]);
                }
            }
        };

        // Quand le formulaire est chargé avec des données existantes
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $requirement = $event->getData();
            if (!$requirement) return;

            $formModifier($event->getForm(), $requirement->getIsFunctional());
        });

        // Quand la checkbox isFunctional est modifiée
        $builder->get('isFunctional')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm()->getParent();
                $isFunctional = $event->getForm()->getData();
                $formModifier($form, $isFunctional);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Requirement::class,
            'csrf_protection' => true, // CSRF activé
        ]);
    }
}
