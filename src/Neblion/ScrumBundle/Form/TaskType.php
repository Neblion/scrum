<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description');
        
        if ($options['remaining']) {
            $builder->add('hour', 'integer', array('read_only' => true));
            $builder->add('remaining_hour', 'integer', array('property_path' => false, 'data' => $options['hours']));
        } else {
            $builder->add('hour');
        }
        
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'remaining' => false,
            'hours'     => 0,
        );
    }

    public function getName()
    {
        return 'neblion_scrumbundle_tasktype';
    }
}
