<?php
// src/Form/PushNotificationType.php
namespace App\Form;

use App\Entity\Ressource;
use App\Repository\RessourceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PushNotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ressources', EntityType::class, [
                'class' => Ressource::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Destinataires',
                'query_builder' => function (RessourceRepository $repo) {
                    return $repo->createQueryBuilder('r')
                        ->innerJoin('r.pushSubscriptions', 'ps') // Assure-toi que cette relation existe
                        ->groupBy('r.id');
                },
                'attr' => [
                    'class' => 'form-control select2',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 4],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Envoyer la notification',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
