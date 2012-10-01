<?php

namespace Neblion\ScrumBundle\Validator\Constraints\ProjectRelease;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StartBeforeReleaseValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    
    public function isValid($value, Constraint $constraint)
    {
        
        $release = $this->em->getRepository('NeblionScrumBundle:ProjectRelease')
                ->hasReleaseBeforeDate($project->getId());
        if ($release) {
            $this->setMessage($constraint->message, array('%string%' => $release->getName(), '%id%' => $project->getId()));
            return false;
        }
        
        return true;
    }
}
