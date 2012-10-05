<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
            
            
            return $this->forward('NeblionScrumBundle:Default:projects');
        }

        return array();
    }
    
    /**
     * @Route("/projects", name="neblion_scrum_projects")
     * @Template()
     */
    public function projectsAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        if (!$user->getProfile()) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You have not completed your profile, please complete it!');
            return $this->redirect($this->generateUrl('profile_new'));
        }
        
        // Check if the user has a pending invitation
        if ($em->getRepository('NeblionScrumBundle:Member')->hasPendingInvitation($user)) {
            return $this->redirect($this->generateUrl('member_invitation'));
        }
        
        $projects = $em->getRepository('NeblionScrumBundle:Project')
                ->getListForUser($user->getId());
        
        // Set the locale (preferred language only)
        // FIXME: we dont have to make it every time !!!!
        //$this->get('session')->setLocale($user->getProfile()->getPreferredLanguage()->getIso2());
        
        /*
        $dql = "SELECT r FROM NeblionScrumBundle:Role r ORDER BY r.id";
        $query = $em->createQuery($dql)
                       ->setFirstResult(2)
                       ->setMaxResults(2);
        
        $paginator = new Paginator($query, $fetchJoinCollection = false);
        
        echo 'count:' . count($paginator) . '<br />';
        foreach ($paginator as $post) {
            echo $post->getName() . "<br />";
        }
        */
        
        return array(
            'projects' => $projects,
            'user'      => $user,
        );
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
