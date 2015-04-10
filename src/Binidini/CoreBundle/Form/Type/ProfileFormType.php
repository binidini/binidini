<?php

namespace Binidini\CoreBundle\Form\Type;

use Binidini\CoreBundle\Entity\User;
use Binidini\CoreBundle\Form\DataTransformer\MaskToBoolTransformer;
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
            ->add('address')
            ->add('type', 'choice',
                [
                    'choices' => [
                        User::TYPE_INDIVIDUAL => 'Персональный',
                        User::TYPE_BUSINESS => 'Юридическое лицо',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                ]
            )
            ->add('aboutMe', 'textarea', ['required' => false])
            ->add('imgPath', 'file', ['required' => false, 'data_class' => null, 'mapped' => true])
            // Блок настроек оповещения
            ->add('smsBidAcceptNotification', 'checkbox', ['required' => false])
            ->add('smsBidAgreeNotification', 'checkbox', ['required' => false])
            ->add('smsBidRejectNotification', 'checkbox', ['required' => false])
            ->add('smsBidRecallNotification', 'checkbox', ['required' => false])
            ->add('smsShippingDeliverNotification', 'checkbox', ['required' => false])
            ->add('smsShippingPayNotification', 'checkbox', ['required' => false])
            ->add('smsShippingCompleteNotification', 'checkbox', ['required' => false])
            ->add('smsShippingRefuseNotification', 'checkbox', ['required' => false])
            ->add('smsShippingDisputeNotification', 'checkbox', ['required' => false])
            ->add('smsShippingDebateNotification', 'checkbox', ['required' => false])

            ->add('emailBidAcceptNotification', 'checkbox', ['required' => false])
            ->add('emailBidAgreeNotification', 'checkbox', ['required' => false])
            ->add('emailBidRejectNotification', 'checkbox', ['required' => false])
            ->add('emailBidRecallNotification', 'checkbox', ['required' => false])
            ->add('emailShippingDeliverNotification', 'checkbox', ['required' => false])
            ->add('emailShippingPayNotification', 'checkbox', ['required' => false])
            ->add('emailShippingCompleteNotification', 'checkbox', ['required' => false])
            ->add('emailShippingRefuseNotification', 'checkbox', ['required' => false])
            ->add('emailShippingDisputeNotification', 'checkbox', ['required' => false])
            ->add('emailShippingDebateNotification', 'checkbox', ['required' => false])

        ;

    }

    public function getName()
    {
        return 'binidini_user_profile';
    }

}