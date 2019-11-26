<?php

namespace App\Form;

use App\Entity\Appareil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppareilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('marque')
            ->add('imei')
            ->add('numSerie')
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Tablette' => 'Tablette',
                    'Smartphone' => 'Smartphone',
                    'Imprimante' => 'Imprimante'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appareil::class,
        ]);
    }
}
