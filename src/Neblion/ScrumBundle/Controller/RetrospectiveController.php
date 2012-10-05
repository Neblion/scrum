<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Retrospective;
use Neblion\ScrumBundle\Form\RetrospectiveType;

/**
 * Retrospective controller.
 *
 * @Route("/retrospective")
 */
class RetrospectiveController extends Controller
{
    /**
     * Lists all Retrospective entities.
     *
     * @Route("/{id}", name="retrospective_list")
     * @Template()
     */
    public function indexAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $form = null;
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        $pathes = array(
            array('label' => 'Home', 'url' => $this->generateUrl('neblion_scrum_welcome')),
            array('label' => 'Projects', 'url' => $this->generateUrl('project_list')),
            array('label' => $project->getName(), 'url' => $this->generateUrl('project_show', array('id' => $project->getId()))),
            array('label' => 'Sprint list', 'url' => $this->generateUrl('sprint_list', array('id' => $project->getId()))),
            array('label' => $sprint->getName(), 'url' => $this->generateUrl('sprint_show', array('id' => $sprint->getId()))),
            array('label' => 'Sprint retrospective', 'url' => ''),
        );
        
        // Get Members of the team
        $members = $em->getRepository('NeblionScrumBundle:Member')
                    ->getTeamMembers($project->getTeam()->getId());
        
        $retrospectives = $em->getRepository('NeblionScrumBundle:Retrospective')
                ->getForSprint($sprint->getId());
        
        $memberRetro = array();
        foreach ($retrospectives as $retrospective) {
            $memberRetro[$retrospective->getUser()->getId()] = $retrospective;
            if ($retrospective->getUser()->getId() == $user->getId()) {
                // Current user has a retro for this sprint
                $form = $this->createForm(new RetrospectiveType(), $retrospective);
            }
        }
        
        if (empty($form)) {
            // Current user with no retro
            $retrospective = new \Neblion\ScrumBundle\Entity\Retrospective();
            $retrospective->setUser($user);
            $form = $this->createForm(new RetrospectiveType(), $retrospective);
        }
        
        return array(
            'project'           => $project,
            'sprint'            => $sprint,
            'members'           => $members,
            'retrospectives'    => $memberRetro,
            'form'              => $form->createView(),
            'pathes'        => $pathes,
        );
    }

    /**
     * Creates a new Retrospective entity.
     *
     * @Route("/{id}/create", name="retrospective_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Retrospective:new.html.twig")
     */
    public function createAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->find($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        $retrospective = new \Neblion\ScrumBundle\Entity\Retrospective();
        $retrospective->setUser($user);
        $retrospective->setSprint($sprint);
        
        $request = $this->getRequest();
        $form    = $this->createForm(new RetrospectiveType(), $retrospective);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($retrospective);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Retrospective was created with success!');
            return $this->redirect($this->generateUrl('retrospective_list', 
                    array('id' => $sprint->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Retrospective entity.
     *
     * @Route("/{id}/edit", name="retrospective_edit")
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

        $retrospective = $em->getRepository('NeblionScrumBundle:Retrospective')->load($id);
        if (!$retrospective) {
            throw $this->createNotFoundException('Unable to find Retrospective entity.');
        }
        
        if ($retrospective->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }
        
        $project = $retrospective->getSprint()->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }

        $editForm = $this->createForm(new RetrospectiveType(), $retrospective);

        return array(
            'project'           => $project,
            'retrospective'      => $retrospective,
            'form'   => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Retrospective entity.
     *
     * @Route("/{id}/update", name="retrospective_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Retrospective:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $retrospective = $em->getRepository('NeblionScrumBundle:Retrospective')->load($id);
        if (!$retrospective) {
            throw $this->createNotFoundException('Unable to find Retrospective entity.');
        }
        
        $project = $retrospective->getSprint()->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }

        $editForm   = $this->createForm(new RetrospectiveType(), $retrospective);
        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($retrospective);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Retrospective was updated with success!');
            return $this->redirect($this->generateUrl('retrospective_list', 
                    array('id' => $retrospective->getSprint()->getId())));
        }

        return array(
            'project'           => $project,
            'retrospective'      => $retrospective,
            'form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a Retrospective entity.
     *
     * @Route("/{id}/delete", name="retrospective_delete")
     * @Method("post")
     */
    /*
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('NeblionScrumBundle:Retrospective')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Retrospective entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('retrospective'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    */
}
