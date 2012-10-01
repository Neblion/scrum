<?php

namespace Neblion\ScrumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class StoryType extends AbstractType
{
    private $project_id;
    
    public function __construct($project_id) 
    {
        $this->project_id = $project_id;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $project_id = $this->project_id;
        $builder
            ->add('feature', 'entity', array(
                'class'         => 'Neblion\ScrumBundle\Entity\Feature',
                'property'      => 'name',
                'query_builder' => function(\Neblion\ScrumBundle\Entity\FeatureRepository $repository) use ($project_id)
                {
                    return $repository->createQueryBuilder('f')
                            ->innerJoin('f.project', 'p')
                            ->where('p.id = :project_id')
                            ->orderBy('f.name')
                            ->setParameter('project_id', $project_id);
                }
            ))
            ->add('name')
            ->add('description');
        
        if (!$options['new']) {
            $builder->add('estimate');
        }
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'new' => false,
        );
    }

    public function getName()
    {
        return 'neblion_scrumbundle_storytype';
    }
}
