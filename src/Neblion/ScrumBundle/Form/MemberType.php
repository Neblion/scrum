<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
         $builder
                ->add('role')
                 ->add('admin')
        ;
        
    }
    
    public function getName()
    {
        return 'neblion_scrumbundle_membertype';
    }
}
