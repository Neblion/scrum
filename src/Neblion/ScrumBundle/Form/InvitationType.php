<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;


class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('required' => true));
            
    }
    
    public function getDefaultOptions(array $options)
    {
        $collectionConstraint = new Collection(array(
            'email' => array(
                new Email(array('message' => 'Invalid email address')),
                new NotBlank(),
            ),
        ));

        return array(
            'validation_constraint' => $collectionConstraint
        );
    }
    
    public function getName()
    {
        return 'neblion_scrumbundle_membertype';
    }
}
