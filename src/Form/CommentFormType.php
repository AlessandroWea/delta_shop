<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\CallbackTransformer;

class CommentFormType extends AbstractType
{

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your name',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please, fill the field!'
                    ])
                ]
            ])
            ->add('text', null, [
                'label' => 'Comment',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Write your comment',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please, write something!'
                    ])
                ]
            ])
            ->add('rating', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'star5' => 5,
                    'star4' => 4,
                    'star3' => 3,
                    'star2' => 2,
                    'star1' => 1,
                ],
                'expanded' => true
            ])
            ->add('product', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'primary-btn'
                ]
            ])
        ;

        $builder->get('product')
            ->addModelTransformer(new CallbackTransformer(
                function() // to form
                {
                    return true;
                },
                function($id) //from form
                {
                    return $this->doctrine->getRepository(Product::class)->find($id);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
