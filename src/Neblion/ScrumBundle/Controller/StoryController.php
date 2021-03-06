<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Story;
use Neblion\ScrumBundle\Form\StoryType;
use Neblion\ScrumBundle\Form\StoryCommentType;
use Neblion\ScrumBundle\Form\EstimateType;

use Doctrine\ORM\Query;
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
        
        $em = $this->getDoctrine()->getManager();
        
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
        
        $em = $this->getDoctrine()->getManager();
        
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
            // Load default story status (To validate)
            $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(4);
            
            // Get last position for story
            $position = $em->getRepository('NeblionScrumBundle:Story')
                    ->getLastPositionForProject($project->getId());
            $story->setPosition($position + 1);
            $story->setStatus($status);
            $em->persist($story);
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'created story', 
                    $this->generateUrl('project_backlog', array('id' => $project->getId())), 
                    'Project #' . $project->getId() . ' ' . $story->getName());
            
            $em->flush();
            
            $success = true;
            
            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('success', 'Story was created with success!');
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
        
        $em = $this->getDoctrine()->getManager();
        
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
        
        // Block story estimate if story was not validate
        if ($story->getStatus()->getId() == 4) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('notice', 'You can not edit estimate, because story was not validate !');
        }

        // Create forms instance
        $editForm = $this->createForm(new StoryType($project->getId()), $story);
        $commentForm = $this->createForm(new StoryCommentType($story));
        
        // Load comments
        $comments = $em->getRepository('NeblionScrumBundle:StoryComment')
                ->loadForStory($story);

        return array(
            'project'       => $project,
            'story'         => $story,
            'form'          => $editForm->createView(),
            'commentForm'   => $commentForm->createView(),
            'comments'      => $comments,
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
        
        $em = $this->getDoctrine()->getManager();
        
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
            // Block story estimate if story was not validate
            if ($story->getStatus()->getId() == 4 and $story->getEstimate() != 0) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('notice', 'You can not edit estimate, because story was not validate !');
                $story->setEstimate(0);
            }
            
            $em->persist($story);
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'updated story', 
                    $this->generateUrl('story_edit', array('id' => $story->getId())), 
                    'Story #' . $story->getId() . ' ' . $story->getName());
            
            $em->flush();
            
            // Set flash message
            $this->get('session')->getFlashBag()->add('success', 'Story was updated with success!');
            if (is_null($story->getSprint())) {
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            } else {
                return $this->redirect($this->generateUrl('sprint_show', array('id' => $story->getSprint()->getId())));
            }
        }

        return array(
            'project'   => $project,
            'story'     => $story,
            'form'      => $editForm->createView(),
        );
    }
    
    /**
     * Validate existing Story entity.
     *
     * @Route("/{id}/validate", name="story_validate")
     * @Template()
     */
    public function validateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
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
        
        // Load To do status
        $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        $story->setStatus($status);
        
        // store activity            
        $this->get('scrum_activity')->add($project, $user, 'validated story', 
           $this->generateUrl('story_edit', array('id' => $story->getId())), 
            'Story #' . $story->getId() . ' ' . $story->getName());
                
        $em->flush();

        // Set flash message
        $this->get('session')->getFlashBag()->add('success', 'Story was validated with success!');
        return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
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
        
        $em = $this->getDoctrine()->getManager();
        
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
                
                // store activity            
                $this->get('scrum_activity')->add($project, $user, 'estimated story', 
                    $this->generateUrl('story_edit', array('id' => $story->getId())), 
                    'Story #' . $story->getId() . ' ' . $story->getName());
                
                $em->flush();
                
                $success = true;

                if (!$this->getRequest()->isXmlHttpRequest()) {
                    // Set flash message
                    $this->get('session')->getFlashBag()->add('success', 'Story was updated with success!');
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
        
        $em = $this->getDoctrine()->getManager();
        
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
            $this->get('session')->getFlashBag()->add('error', 'Story could not be deleted !');
            return $this->redirect($this->generateUrl('project_backlog'));
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->remove($story);
                
                // store activity            
                $this->get('scrum_activity')->add($project, $user, 'deleted story', 
                    $this->generateUrl('project_backlog', array('id' => $project->getId())), 
                    'Story #' . $story->getId() . ' ' . $story->getName());
                
                $em->flush();
            }

            // Set flash message
            $this->get('session')->getFlashBag()->add('success', 'Story was deleted with success !');
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
        
        $em = $this->getDoctrine()->getManager();
        
        $story = $em->getRepository('NeblionScrumBundle:Story')->load($id, Query::HYDRATE_OBJECT);
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
        
        // Prepare results
        $storyDetails['id']               = $story->getId();
        $storyDetails['name']             = $story->getName();
        $storyDetails['description']      = $story->getDescription();
        $storyDetails['estimate']         = $story->getEstimate();
        $storyDetails['position']         = $story->getPosition();
        if (!is_null($story->getFeature())) {
            $storyDetails['feature']          = array('name' => $story->getFeature()->getName(), 'color' => $story->getFeature()->getColor());
        } else {
            $storyDetails['feature']            = array('name' => null, 'color' => '#ffffff');
        }
        $storyDetails['status']           = $story->getStatus()->getName();
        $storyDetails['type']['id']       = $story->getType()->getId();
        $storyDetails['type']['name']     = $story->getType()->getName();
        $storyDetails['remainingHours']   = 0;
        $storyDetails['totalHours']   = 0;
        $storyDetails['tasks']            = array(
            1 => array(),
            2 => array(),
            3 => array(),
        );
        
        // Load tasks
        $tasks = $em->getRepository('NeblionScrumBundle:Task')->loadForStory($story->getId());
        
        $remainingHours = 0;
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
                $remainingHours += $taskArray['remaining_hour'];
                $storyDetails['tasks'][$task['status']['id']][] = $taskArray;
                $storyDetails['totalHours'] += $task['hour'];
        }
        $storyDetails['remainingHours'] = $remainingHours;
        
        return $this->container->get('templating')
                ->renderResponse('NeblionScrumBundle:Story/Ajax:loadTasks.html.twig', 
                        array('story' => $storyDetails, 'sprint' => $story->getSprint()));
    }
    
}
