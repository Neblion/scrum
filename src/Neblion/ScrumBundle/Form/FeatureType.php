<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FeatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('color', 'text', array('attr' => array('class' => 'color-picker')))
        ;
    }

    public function getName()
    {
        return 'neblion_scrumbundle_featuretype';
    }
}
