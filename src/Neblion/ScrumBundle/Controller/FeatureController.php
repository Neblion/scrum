<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Feature;
use Neblion\ScrumBundle\Form\FeatureType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Feature controller.
 *
 * @Route("/feature")
 */
class FeatureController extends Controller
{
    /**
     * Lists all Feature entities.
     *
     * @Route("/{id}/list", name="feature_list")
     * @Template()
     */
    public function indexAction($id, $page = 1)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        // Load project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        $features = $em->getRepository('NeblionScrumBundle:Feature')
                ->getListForProject($project->getId());
        
        return array(
            'project'       => $project,
            'features'      => $features,
        );
    }

    /**
     * Displays a form to create a new Feature entity.
     * 
     * Only scrumaster and product owner or admin of project
     * could add a new feature.
     *
     * @Route("/{id}/new", name="feature_new")
     * @Template()
     */
    public function newAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        // Load current project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        $success = false;
        $feature = new Feature();
        $feature->setProject($project);
        $form   = $this->createForm(new FeatureType(), $feature);
        
        return array(
            'project' => $project,
            'feature' => $feature,
            'form' => $form->createView(),
            'success' => $success,
        );
    }

    /**
     * Creates a new Feature entity.
     *
     * @Route("/{id}/create", name="feature_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Feature:new.html.twig")
     */
    public function createAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        // Load current project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        $success = false;
        $feature  = new Feature();
        $feature->setProject($project);
        $request = $this->getRequest();
        $form    = $this->createForm(new FeatureType(), $feature);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($feature);
            $em->flush();
            
            $success = true;

            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->setFlash('success', 'Feature was created with success!');
                return $this->redirect($this->generateUrl('feature_list', array('id' => $project->getId())));
            }
        }

        return array(
            'project' => $project,
            'feature' => $feature,
            'form' => $form->createView(),
            'success' => $success,
        );
    }

    /**
     * Displays a form to edit an existing Feature entity.
     *
     * @Route("/{id}/edit", name="feature_edit")
     * @Template()
     */
    public function editAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $feature = $em->getRepository('NeblionScrumBundle:Feature')->find($id);
        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }
        
        // Load current project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($feature->getProject()->getId());
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        $editForm = $this->createForm(new FeatureType(), $feature);

        return array(
            'project'   => $project,
            'feature'   => $feature,
            'form'      => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Feature entity.
     *
     * @Route("/{id}/update", name="feature_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Feature:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $feature = $em->getRepository('NeblionScrumBundle:Feature')->find($id);
        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }
        
        // Load current project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($feature->getProject()->getId());
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        $editForm   = $this->createForm(new FeatureType(), $feature);
        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($feature);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Feature was updated with success!');
            return $this->redirect($this->generateUrl('feature_list', array('id' => $project->getId())));
        }

        return array(
            'project'   => $project,
            'feature'   => $feature,
            'form'      => $editForm->createView(),
        );
    }

    /**
     * Deletes a Feature entity.
     *
     * @Route("/{id}/delete", name="feature_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $feature = $em->getRepository('NeblionScrumBundle:Feature')->load($id);
        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }
        
        // Load current project
        $project = $feature->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        $stories = $feature->getStories();
        if (count($stories) != 0) {
            // Set flash message
            $this->get('session')->setFlash('notice', 'You could not delete this Feature, stories associated with it!');
            return $this->redirect($this->generateUrl('feature_list', array('id' => $project->getId())));
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->remove($feature);
                $em->flush();
            }

            // Set flash message
            $this->get('session')->setFlash('success', 'Feature was deleted with success!');
            return $this->redirect($this->generateUrl('feature_list', array('id' => $project->getId())));
        }
        
        return array(
            'project'   => $project,
            'feature'   => $feature,
            'form'      => $form->createView(),
        );
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
