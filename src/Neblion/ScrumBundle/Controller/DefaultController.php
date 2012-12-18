<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\Validator\Constraints\Email;

class DefaultController extends Controller
{
    
    
    /**
     * @Route("/", name="neblion_scrum_welcome")
     * @Template()
     */
    public function indexAction()
    {
        // Check if user is authorized
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // Check if the user has a profile, if not redirect to profile_new
            $user = $this->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getEntityManager();
            
            // Check if user has a profile
            if (!$user->getProfile()) {
                return $this->redirect($this->generateUrl('profile_new'));
            }
            
            // Check if username of user is an email
            $emailConstraint = new Email();
            // use the validator to validate the value
            $errorList = $this->get('validator')->validateValue($user->getUsername(), $emailConstraint);
            if (count($errorList) == 0) {
                // Set flash message
                $this->get('session')->setFlash('notice', 'You should change your username, username should not be an email !');
                return $this->redirect($this->generateUrl('profile_username_email'));
            }
            
            return $this->forward('NeblionScrumBundle:Project:projects');
        }

        return array();
    }
    
    /**
     * @Route("/activity", name="activity")
     * @Template()
     */
    public function activityAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        // Load user's related activities
        $activities = $em->getRepository('NeblionScrumBundle:Activity')->loadRelatedForAccount($user, false, 20);
        
        return array(
            'activities'    => $activities,
            'user'          => $user,
        );
    }
    
}
