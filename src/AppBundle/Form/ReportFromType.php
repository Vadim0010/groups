<?php

namespace AppBundle\Form;

use AppBundle\Entity\Reports;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportFromType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'data' => $user ? $user->getUsername() : '',
                'constraints' => [
                    new Length (['max' => 255]),
                    new NotBlank()
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'data' => $user ? $user->getEmail() : '',
                'constraints' => [
                    new Email()
                ]
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reports::class,
            'user' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'report_form_type';
    }
}
