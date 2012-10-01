<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SprintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('start', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 
                'attr' => array('class' => 'date-picker')))
            ->add('end', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date-picker')))
            //->add('created')
            //->add('updated')
            //->add('projectRelease')
            //->add('status')
            //->add('lastStory', 'hidden', array('property_path' => false, 'data' => $options['lastStory']))
        ;
    }

    public function getName()
    {
        return 'neblion_scrumbundle_sprinttype';
    }
    
}
