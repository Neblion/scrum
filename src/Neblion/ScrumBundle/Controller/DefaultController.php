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
     * @Route("/invitation/confirm/{token}", name="scrum_invitation_confirm")
     * @Template()
     */
    public function confirmInvitationAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }
        
        $form = $this->container->get('fos_user.registration.form');
        $form->setData($user);
        
        if ('POST' === $this->getRequest()->getMethod()) {
            $form->bindRequest($this->getRequest());
            
            if ($user->getUsername() == $user->getEmail()) {
                $form->addError(new FormError('username = email'));
            }

            if ($form->isValid()) {
                $user->setConfirmationToken(null);
                $user->setEnabled(true);
                $user->setLastLogin(new \DateTime());

                $this->container->get('fos_user.user_manager')->updateUser($user);
                $this->authenticateUser($user);
                
                // Set flash message
                return $this->redirect($this->generateUrl('neblion_scrum_dashboard'));
            }
        }
        
        return array(
            'form' => $form->createView(),
            'token' => $token,
            'theme' => $this->container->getParameter('fos_user.template.theme'),
        );
    }
    
}
