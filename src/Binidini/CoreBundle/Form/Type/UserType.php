<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('patronymic')
            ->add('companyName')
            ->add('city')
            ->add('address')
            ->add('type', 'choice',
                [
                    'choices' => [
                        User::TYPE_INDIVIDUAL => 'Физическое лицо',
                        User::TYPE_BUSINESS => 'Юридическое лицо',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                ]
            )
            ->add('profileType', 'choice',
                [
                    'choices' => [
                        User::PROFILE_TYPE_CARRIER_AND_SENDER => 'Отправитель и перевозчик',
                        User::PROFILE_TYPE_CARRIER => 'Перевозчик',
                        User::PROFILE_TYPE_SENDER => 'Отправитель',

                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                ]
            )
            ->add('aboutMe', 'textarea', ['required' => false])
            ->add('imgPath', 'file', ['required' => false, 'data_class' => null, 'mapped' => true])
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Binidini\CoreBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'binidini_core_user';
    }
}