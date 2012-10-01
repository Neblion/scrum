<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectReleaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('status')
            ->add('start', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 
                'attr' => array('class' => 'date-picker')))
            ->add('end', 'date', array('widget' => 'single_text', 'required'=> false, 'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date-picker')))
        ;
    }

    public function getName()
    {
        return 'neblion_scrumbundle_projectreleasetype';
    }
}
