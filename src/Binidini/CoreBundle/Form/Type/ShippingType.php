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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShippingType extends AbstractType
{

    public function __construct(Container $container)
    {
        $request = $container->get('request');
        $this->routeName = $request->get('_route');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('pickupAddress')
            ->add('deliveryAddress')
            ->add('deliveryDatetime', 'datetime', ['widget' => 'single_text', 'format' => 'dd.MM.yy HH:mm'])
            ->add('deliveryPrice')
            ->add('paymentGuarantee', 'checkbox', ['required' => false])
            ->add('insurance', null, ['required' => false])
            ->add('deliveryCode', 'checkbox', ['required' => false])
            ->add('weight')
            ->add('x')
            ->add('y')
            ->add('z');

        $builder->get('deliveryCode')
            ->addModelTransformer(new CallbackTransformer(
                function ($originalDeliveryCode) {
                    if ($originalDeliveryCode > 0 )
                        return true;
                    else
                        return false;
                },
                function ($submittedDeliveryCode) {
                    if ($submittedDeliveryCode)
                        return 1;
                    else
                        return 0;
                }
            ))
        ;

        if ($this->routeName == "binidini_api_shipping_new") {
            $builder
                ->add('imgBase64', 'textarea', ['required' => false, 'data_class' => null, 'mapped' => true])
                ->add('fileName', 'textarea', ['required' => false, 'data_class' => null, 'mapped' => true]);
        } else {
            $builder->add('imgFile', 'file', ['required' => false]);
        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        if ($this->routeName == "binidini_api_shipping_new") {
            $resolver->setDefaults(array(
                'data_class' => 'Binidini\CoreBundle\Entity\Shipping',
                'csrf_protection' => false
            ));
        } else {
            $resolver->setDefaults(array(
                'data_class' => 'Binidini\CoreBundle\Entity\Shipping',
            ));
        }
    }

    public function getName()
    {
        return 'binidini_core_shipping';
    }
}