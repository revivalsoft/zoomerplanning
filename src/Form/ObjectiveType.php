<?php
namespace App\Form;

use App\Entity\Objective;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{TextType, TextareaType, DateTimeType, CheckboxType};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre',])
            ->add('description', TextareaType::class, ['label' => 'Description','required' => false])
            ->add('startDate', DateTimeType::class, ['label' => 'Date début'])
            ->add('endDate', DateTimeType::class, ['label' => 'Date fin'])
            ->add('isClosed', CheckboxType::class, ['label' => 'Fermé','required' => false])
            ->add('isPublic', CheckboxType::class, ['label' => 'Public','required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Objective::class]);
    }
}
