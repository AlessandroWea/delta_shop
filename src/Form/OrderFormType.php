<?php

namespace App\Form;

use App\Entity\Orders;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('sessionId',HiddenType::class)
            ->add('price')
            ->add('quantity')
            ->add('customer_name')
            ->add('email')
            ->add('phone', TextType::class, [
                'label' => 'Telephone',
            ]
        )

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}
