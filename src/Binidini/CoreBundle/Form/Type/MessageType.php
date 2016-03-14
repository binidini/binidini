<?php

namespace Binidini\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class MessageType extends AbstractType
{
    private $sender;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', 'textarea')
            ->add('shipping', 'entity_hidden', ['data_class' => 'Binidini\CoreBundle\Entity\Shipping']);

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Binidini\CoreBundle\Entity\Message',
        ));
    }

    public function getName()
    {
        return 'binidini_core_message';
    }
}