<?php

namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use App\Entity\SousCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categories', EntityType::class,[
                'label' => false,
                'required' => false,
                'class' => Category::class,
                'multiple' => true,
                'expanded' => true
            ])

            ->add('souscategories', EntityType::class,[
                'label' => false,
                'required' => false,
                'class' => SousCategory::class,
                'multiple' => true,
                'expanded' => true
            ])

            ->add('submit', SubmitType::class,[
                'label' => 'Filtrer',
                'attr' => [
                    'class' =>'button-new'
                ]
                
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'crsf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}