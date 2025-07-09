<?php
// src/Form/DependencyType.php

namespace App\Form;

use App\Entity\Dependency;
use App\Entity\Gtask;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DependencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fromGtask', EntityType::class, [
                'class' => Gtask::class,
                'choice_label' => 'name',
                'label' => 'Tâche dépendante',
            ])
            ->add('toGtask', EntityType::class, [
                'class' => Gtask::class,
                'choice_label' => 'name',
                'label' => 'Dépend de la tâche',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dependency::class,
        ]);
    }
}
