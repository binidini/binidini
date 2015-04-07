<?php

namespace Binidini\CoreBundle\Form\Type;

use Binidini\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileFormType extends \FOS\UserBundle\Form\Type\ProfileFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', ['required' => false])
            ->add('firstName')
            ->add('lastName')
            ->add('patronymic')
            ->add('companyName')
            ->add('type', 'choice',
                [
                    'choices' => [
                        User::TYPE_INDIVIDUAL => 'Курьер',
                        User::TYPE_BUSINESS => 'Компания',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                ]
            )
            ->add('aboutMe', 'textarea', ['required' => false])
            ->add('imgPath', 'file', ['required' => false, 'data_class' => null, 'mapped' => true]);
    }

    public function getName()
    {
        return 'binidini_user_profile';
    }

}