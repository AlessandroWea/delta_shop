<?php

declare(strict_types=1);

namespace App\Admin;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\MediaBundle\Form\Type\MediaType;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Category;

use App\Repository\CategoryRepository;

final class ProductAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('name')
            ->add('description')
            ->add('price')
            //->add('image')

            ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('name')
            ->add('description')
            ->add('price')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
            ->add('image', MediaType::class, [
                'provider' => 'sonata.media.provider.image',
                'context'  => 'product',
                'required' => false,
                'label'    => 'Изображение',
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name')
            ->add('description', CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    //...
                )))
            ->add('price')
            ->add('category', EntityType::class , [
                'class' => Category::class ,
                'query_builder' => function (CategoryRepository $repo){
                    return $repo->createQueryBuilder('c')
                        ->orderBy('c.position', 'ASC');
                },
                'choice_label' => function($category){
                    return $category->getLeveledName();
                },
                
            ])
            ->add('image', MediaType::class, [
                'provider' => 'sonata.media.provider.image',
                'context'  => 'product',
                'required' => false,
                'label'    => 'Изображение',
            ])
            ->add('gallery', AdminType::class);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('name')
            ->add('description')
            ->add('price')
            ;
    }
}
