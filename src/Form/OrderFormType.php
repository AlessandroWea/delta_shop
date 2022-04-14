<?php

namespace App\Form;

use App\Entity\Orders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('sessionId',HiddenType::class)
            //->add('price')
            //->add('quantity')
            ->add('customer_name', TextType::class, [
                'label' => 'Enter your fullname'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Enter your email',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => 'autofocus',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please fill the field'
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email'
                    ])
                ]
            ])
            ->add('phone', TelType::class, [
                    'label' => 'Enter your phone number']
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
