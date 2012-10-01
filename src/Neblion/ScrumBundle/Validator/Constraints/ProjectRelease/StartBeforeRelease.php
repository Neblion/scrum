<?php

namespace Neblion\ScrumBundle\Validator\Constraints\ProjectRelease;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StartBeforeRelease extends Constraint
{
    public $message = '(%id%) The release %string% has no due date, so you could not create a new one!';
    
    public function validatedBy()
    {
        return 'release_startbeforerelease';
    }
    
    
}