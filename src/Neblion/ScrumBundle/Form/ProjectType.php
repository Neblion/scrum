<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class ProjectType extends AbstractType
{
    private $translator;
    
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('sprint_start_day', 'choice', array(
                'choices' => array(
                    0 => $this->translator->trans('Sunday'), 
                    1 => $this->translator->trans('Monday'), 
                    2 => $this->translator->trans('Tuesday'),
                    3 => $this->translator->trans('Wednesday'),
                    4 => $this->translator->trans('Thursday'),
                    5 => $this->translator->trans('Friday'),
                    6 => $this->translator->trans('Saturday'))
            ))
            ->add('sprint_duration')
            ->add('is_public', 'choice', array(
                'choices' => array(
                    1 => 'Public',
                    0 => 'Private',
                ),
            ))
        ;
    }

    public function getName()
    {
        return 'neblion_scrumbundle_projecttype';
    }
}
