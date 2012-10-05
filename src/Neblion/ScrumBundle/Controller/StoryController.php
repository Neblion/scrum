<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Story;
use Neblion\ScrumBundle\Form\StoryType;
use Neblion\ScrumBundle\Form\EstimateType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Story controller.
 *
 * @Route("/story")
 */
class StoryController extends Controller
{
    
    
    /**
     * Displays a form to create a new Story entity.
     *
     * @Route("/{id}/new", name="story_new")
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
        
        // Load project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }
        
        $success = false;
        $story = new Story();
        $story->setProject($project);
        $form   = $this->createForm(new StoryType($project->getId()), $story, array('new' => true));

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Story/Ajax:new.html.twig', array(
                'project'   => $project,
                'story'     => $story,
                'form'      => $form->createView(),
                'success'   => $success,
            ));
        } else {
            return array(
                'project'   => $project,
                'story'     => $story,
                'form'      => $form->createView(),
                'success'   => $success,
            );
        }
    }

    /**
     * Creates a new Story entity.
     *
     * @Route("/{id}/create", name="story_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Story:new.html.twig")
     */
    public function createAction($id)
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
        
        $success = false;
        $story = new Story();
        $story->setProject($project);
        $form   = $this->createForm(new StoryType($project->getId()), $story, array('new' => true));
        
        $request = $this->getRequest();
        $form->bindRequest($request);

        if ($form->isValid()) {
            // Load default sprint status
            $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
            
            // Get last position for story
            $position = $em->getRepository('NeblionScrumBundle:Story')
                    ->getLastPositionForProject($project->getId());
            $story->setPosition($position + 1);
            $story->setStatus($status);
            $em->persist($story);
            $em->flush();
            
            $success = true;
            
            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->setFlash('success', 'Story was created with success!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Story/Ajax:new.html.twig', array(
                'project'   => $project,
                'story'     => $story,
                'form'      => $form->createView(),
                'success'   => $success,
            ));
        } else {
            return array(
                'project'   => $project,
                'story'     => $story,
                'form'      => $form->createView(),
            );
        }
    }

    /**
     * Displays a form to edit an existing Story entity.
     *
     * @Route("/{id}/edit", name="story_edit")
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
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->find($id);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }

        $editForm = $this->createForm(new StoryType($project->getId()), $story);

        return array(
            'project'   => $project,
            'story'     => $story,
            'form'      => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Story entity.
     *
     * @Route("/{id}/update", name="story_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Story:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->find($id);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }

        $editForm   = $this->createForm(new StoryType($project->getId()), $story);
        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($story);
            $em->flush();
            
            // Set flash message
            $this->get('session')->setFlash('success', 'Story was updated with success!');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }

        return array(
            'project'   => $project,
            'story'     => $story,
            'form'      => $editForm->createView(),
        );
    }
    
    /**
     * Displays a form to edit estimate of an existing Story entity.
     *
     * @Route("/{id}/estimate", name="story_estimate")
     * @Template()
     */
    public function estimateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->find($id);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }

        $success = false;
        $form = $this->createForm(new EstimateType(), $story);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($story);
                $em->flush();
                
                $success = true;

                if (!$this->getRequest()->isXmlHttpRequest()) {
                    // Set flash message
                    $this->get('session')->setFlash('success', 'Story was updated with success!');
                    return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
                }
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Story/Ajax:estimate.html.twig', array(
                'project'   => $project,
                'story'     => $story,
                'form'      => $form->createView(),
                'success'   => $success,
            ));   
        } else {
            return array(
                'project'   => $project,
                'story'     => $story,
                'form'      => $form->createView(),
            );
        }
    }

    /**
     * Deletes a Story entity.
     * 
     * Only product owner and admin could delete a story
     * Only story not assign to a sprint could be deleted
     *
     * @Route("/{id}/delete", name="story_delete")
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
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->find($id);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() !=  1) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        // Check if story could be deleted
        // Only story not assign to a sprint could be deleted
        if ($story->getSprint()) {
            // Set flash message
            $this->get('session')->setFlash('error', 'Story could not be deleted !');
            return $this->redirect($this->generateUrl('project_backlog'));
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->remove($story);
                $em->flush();
            }

            // Set flash message
            $this->get('session')->setFlash('success', 'Story was deleted with success !');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }
        
        return array(
            'project'   => $project,
            'story'     => $story,
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
    
    /**
     * Load Tasks for a story
     * 
     * Load tasks for a story
     *
     * @Route("/{id}/tasks", name="story_tasks")
     * @Template()
     * @param integer $id Story id
     */
    public function loadTasksAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->find($id);
        if (!$story) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        $project = $story->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Load tasks
        $tasks = $em->getRepository('NeblionScrumBundle:Task')->loadForStory($story->getId());
        
        // Prepare results
        $resultTasks = array();
        foreach ($tasks as $task) {
                $taskArray = array(
                    'id' => $task['id'],
                    'name' => $task['name'],
                    'description' => $task['description'],
                    'hour' => $task['hour'],
                    'status' => array('id' => $task['status']['id'], 'name' => $task['status']['name']),
                    
                );
                if (!empty($task['member'])) {
                    $taskArray['member']  = $task['member'];
                } else {
                    $taskArray['member']  = '';
                }
                if (empty($task['hours'])) {
                    $taskArray['remaining_hour'] = $task['hour'];
                } else {
                    $taskArray['remaining_hour'] = $task['hours'][0]['hour'];
                }
                
                $resultTasks[$task['status']['id']][] = $taskArray;
        }
        
        return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:loadToDo.html.twig', array(
            'tasks' => $resultTasks,
        ));
    }
    
}
