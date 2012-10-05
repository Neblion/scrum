<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('sprint_start_day', 'choice', array(
                'choices' => array(0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
                    3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday')
            ))
            ->add('sprint_duration')
        ;
    }

    public function getName()
    {
        return 'neblion_scrumbundle_projecttype';
    }
}
