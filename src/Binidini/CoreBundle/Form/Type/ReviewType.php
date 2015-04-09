<?php

namespace Binidini\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', 'textarea')
            ->add('rating', 'hidden')
            ->add('shipping', 'entity_hidden', ['data_class' => 'Binidini\CoreBundle\Entity\Shipping'])
            ->add('userTo', 'entity_hidden', ['data_class' => 'Binidini\CoreBundle\Entity\User']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Binidini\CoreBundle\Entity\Review',
        ));
    }

    public function getName()
    {
        return 'binidini_core_review';
    }
}