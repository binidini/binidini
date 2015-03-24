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
use Symfony\Component\Security\Core\SecurityContext;

class ShippingType extends AbstractType
{
    private $sender;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('pickupAddress')
            ->add('deliveryAddress')
            ->add('pickupDatetime', 'datetime', ['widget' =>'single_text', 'format' => 'dd.MM.yy HH:mm'])
            ->add('deliveryDatetime', 'datetime', ['widget' =>'single_text', 'format' => 'dd.MM.yy HH:mm'])
            ->add('deliveryPrice')
            ->add('guaranteeCost')
            ->add('weight')
            ->add('x')
            ->add('y')
            ->add('z');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Binidini\CoreBundle\Entity\Shipping',
        ));
    }

    public function getName()
    {
        return 'binidini_core_shipping';
    }
}