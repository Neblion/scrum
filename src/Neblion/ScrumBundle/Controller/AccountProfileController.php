<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Profile;
use Neblion\ScrumBundle\Form\ProfileType;

/**
 * Profile controller.
 *
 * @Route("/profile")
 */
class AccountProfileController extends Controller
{
    /**
     * Lists all Profile entities.
     *
     * @Route("/", name="profile")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NeblionScrumBundle:Profile')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Profile entity.
     *
     * @Route("/{id}/show", name="profile_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NeblionScrumBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Profile entity.');
        }
        
        /*
        $userManager = $this->container->get('fos_user.user_manager');
        $account = $userManager->findUserByUsernameOrEmail('thomas.bibard+4@neblion.net');
        $account->setUsername('scrum');
        $userManager->updateUser($account);
        */

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Profile entity.
     *
     * @Route("/new", name="profile_new")
     * @Template()
     */
    public function newAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $account = $this->get('security.context')->getToken()->getUser();
        
        // Check if the user has already a profile
        if ($account->getProfile()) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You have already a profile, edit it!');
            return $this->redirect($this->generateUrl('profile_edit', array('id' => $account->getProfile()->getId())));
        }
        
        $profile = new Profile();
        $profile->setAccount($account);
        $form   = $this->createForm(new ProfileType(), $profile);
        
        return array(
            'entity'    => $profile,
            'form'      => $form->createView()
        );
    }

    /**
     * Creates a new Profile entity.
     *
     * @Route("/create", name="profile_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Profile:new.html.twig")
     */
    public function createAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $account = $this->get('security.context')->getToken()->getUser();
        
        // Check if the user has already a profile
        if ($account->getProfile()) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You have already a profile, edit it!');
            return $this->redirect($this->generateUrl('profile_edit', array('id' => $account->getProfile()->getId())));
        }
        
        $entity  = new Profile();
        $entity->setAccount($account);
        $request = $this->getRequest();
        $form    = $this->createForm(new ProfileType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();
            
            // Set flash message
            $this->get('session')->setFlash('success', 'Profile was successfully created!');
            return $this->redirect($this->generateUrl('neblion_scrum_welcome'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Profile entity.
     *
     * @Route("/edit", name="profile_edit")
     * @Template()
     */
    public function editAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $account = $this->get('security.context')->getToken()->getUser();
        $profile = $account->getProfile();
        
        // Check if the user has already a profile
        if (!$profile) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You have not a profile, create it!');
            return $this->redirect($this->generateUrl('profile_new'));
        }
        
        $form           = $this->createForm(new ProfileType(), $profile);
        $form_account   = $this->container->get('fos_user.profile.form');
        $formHandler = $this->container->get('fos_user.profile.form.handler');
        $process = $formHandler->process($account);

        return array(
            'profile'   => $profile,
            'form'      => $form->createView(),
            'form_account'      => $form_account->createView()
        );
    }
    
    /**
     * Displays a form to edit username or email.
     *
     * @Route("/username-email", name="profile_username_email")
     * @Template()
     */
    public function usernameEmailAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $account = $this->get('security.context')->getToken()->getUser();
        $profile = $account->getProfile();
        
        // Check if the user has already a profile
        if (!$profile) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You have not a profile, create it!');
            return $this->redirect($this->generateUrl('profile_new'));
        }
        
        $form   = $this->container->get('fos_user.profile.form');
        $formHandler = $this->container->get('fos_user.profile.form.handler');
        $process = $formHandler->process($account);
        if ($process) {
            // set flash message and redirect
            $this->get('session')->setFlash('success', 'Username and/or email was updated with success !');
            return $this->redirect($this->generateUrl('profile_username_email'));
        }

        return array(
            'form'      => $form->createView(),
        );
    }

    /**
     * Edits an existing Profile entity.
     *
     * @Route("/update", name="profile_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Profile:edit.html.twig")
     */
    public function updateAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $account = $this->get('security.context')->getToken()->getUser();
        $profile = $account->getProfile();
        
        // Check if the user has already a profile
        if (!$profile) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You have not a profile, create it!');
            return $this->redirect($this->generateUrl('profile_new'));
        }
        
        $form    = $this->createForm(new ProfileType(), $profile);
        $request = $this->getRequest();
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($profile);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Profile was successfully updated!');
            return $this->redirect($this->generateUrl('profile_edit'));
        }

        return array(
            'profile'       => $profile,
            'form'          => $form->createView(),
        );
    }

    /**
     * Deletes a Profile entity.
     *
     * @Route("/{id}/delete", name="profile_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NeblionScrumBundle:Profile')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Profile entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('profile'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
