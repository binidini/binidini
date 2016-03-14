<?php

namespace Binidini\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BidType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price')
            ->add('comment', 'textarea', ['required' => false])
            ->add('shipping', 'entity_hidden', ['data_class' => 'Binidini\CoreBundle\Entity\Shipping']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Binidini\CoreBundle\Entity\Bid',
        ));
    }

    public function getName()
    {
        return 'binidini_core_bid';
    }
}