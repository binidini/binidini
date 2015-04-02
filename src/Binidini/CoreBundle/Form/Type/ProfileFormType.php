<?php

namespace Binidini\CoreBundle\Form\Type;

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
            ->add('aboutMe', 'textarea', ['required' => false])
            //->add('imgPath', 'file', array('required' => false, 'data_class' => null, 'mapped' => true))
        ;
    }

    public function getName()
    {
        return 'binidini_user_profile';
    }

}