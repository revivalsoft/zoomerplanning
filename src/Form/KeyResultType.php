<?php
namespace App\Form;

use App\Entity\KeyResult;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeyResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('initialValue', NumberType::class, [
                'label' => 'Valeur initiale',
                'required' => true,
                'scale' => 2,
            ])
            ->add('targetValue', NumberType::class, [
                'label' => 'Valeur cible',
            ])
            ->add('currentValue', NumberType::class, [
                'label' => 'Valeur courante',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => KeyResult::class,
        ]);
    }
}
