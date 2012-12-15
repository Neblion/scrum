<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('comment', 'textarea', array(
            'attr' => array('rows' => 5),
        ));
    }

    public function getName()
    {
        return 'neblion_scrumbundle_reviewtype';
    }
}
