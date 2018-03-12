<?php

namespace AppBundle\Form;

use AppBundle\EventListener\SearchListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SearchFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['max' => 100])
                ]
            ])
            ->add(
                $builder->create('subscribers', FormType::class, ['inherit_data' => false])
                    ->add('from', NumberType::class, [
                        'required' => false,
                        'grouping' => true,
                        'constraints' => [
                            new Type(['type' => 'numeric']),
                            new Range(['min' => 0])
                        ]
                    ])
                    ->add('to', NumberType::class, [
                        'required' => false,
                        'grouping' => true,
                        'constraints' => [
                            new Type(['type' => 'numeric']),
                            new Range(['min' => 0])
                        ]
                    ])
            )
            ->add(
                $builder->create('price', FormType::class, ['inherit_data' => false])
                    ->add('from', MoneyType::class, [
                        'required' => false,
                        'grouping' => true,
                        'constraints' => [
                            new Type(['type' => 'float']),
                            new Range(['min' => 0])
                        ],
                    ])
                    ->add('to', MoneyType::class, [
                        'required' => false,
                        'grouping' => true,
                        'constraints' => [
                            new Type(['type' => 'float']),
                            new Range(['min' => 0])
                        ]
                    ])
            )
            ->add(
                $builder->create('gain', FormType::class, ['inherit_data' => false])
                    ->add('from', MoneyType::class, [
                        'required' => false,
                        'grouping' => true,
                        'constraints' => [
                            new Type(['type' => 'float']),
                            new Range(['min' => 0])
                        ]
                    ])
                    ->add('to', MoneyType::class, [
                        'required' => false,
                        'grouping' => true,
                        'constraints' => [
                            new Type(['type' => 'float']),
                            new Range(['min' => 0])
                        ]
                    ])
            )
            ->add('isVerify', CheckboxType::class, [
                'required' => false,
                'value' => false,
                'constraints' => [
                    new Type(['type' => 'bool'])
                ]
            ])
            ->addEventSubscriber(new SearchListener())
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET'
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
