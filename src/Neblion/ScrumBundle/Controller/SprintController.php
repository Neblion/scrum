<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Sprint;
use Neblion\ScrumBundle\Form\SprintType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\Form\FormError;

/**
 * Sprint controller.
 *
 * @Route("/sprint")
 */
class SprintController extends Controller
{
    /**
     * Lists all Sprint entities.
     *
     * @Route("/{id}", name="sprint_list")
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
        
        // Load current project
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
        
        $sprints = $em->getRepository('NeblionScrumBundle:Sprint')->getList($project->getId());
        
        $velocity = $em->getRepository('NeblionScrumBundle:Project')->getVelocity(1);
        //echo 'velocity=' . floor($velocity) . '<br />';
        
        return array(
            'project'       => $project,
            'sprints'       => $sprints,
        );
    }

    /**
     * Finds and displays a Sprint entity.
     *
     * @Route("/{id}/show", name="sprint_show")
     * @Template()
     */
    public function showAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

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
        
        $sprintDetails = $em->getRepository('NeblionScrumBundle:Story')->getSprintDetails($id);
        $sprintInfos = array(
            'estimate'  => array('todo' => 0, 'done' => 0),
            'hours'     => 0,
        );
        $storyAndTasksByStatus = array();
        foreach ($sprintDetails as $story) {
            $storyAndTasksByStatus[$story['id']]['id']        = $story['id'];
            $storyAndTasksByStatus[$story['id']]['name']        = $story['name'];
            $storyAndTasksByStatus[$story['id']]['description'] = $story['description'];
            $storyAndTasksByStatus[$story['id']]['estimate']    = $story['estimate'];
            $storyAndTasksByStatus[$story['id']]['position']    = $story['position'];
            $storyAndTasksByStatus[$story['id']]['feature']     = $story['feature'];
            $storyAndTasksByStatus[$story['id']]['status']     = $story['status']['name'];
            $storyAndTasksByStatus[$story['id']]['type']['id'] = $story['type']['id'];
            $storyAndTasksByStatus[$story['id']]['type']['name'] = $story['type']['name'];
            $storyAndTasksByStatus[$story['id']]['remainingHours']     = 0;
            $storyAndTasksByStatus[$story['id']]['tasks'] = array(
                1 => array(),
                2 => array(),
                3 => array(),
            );
            
            // sprintInfos
            if ($story['status']['id'] == 3) {
                $sprintInfos['estimate']['done'] += $story['estimate'];
            } else {
                $sprintInfos['estimate']['todo'] += $story['estimate'];
            }
            
            foreach ($story['tasks'] as $task) {
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
                
                if (in_array($task['status']['id'], array(1, 2))) {
                    $storyAndTasksByStatus[$story['id']]['remainingHours'] += $taskArray['remaining_hour'];
                }
                
                $storyAndTasksByStatus[$story['id']]['tasks'][$task['status']['id']][] = $taskArray;
                
                // sprintInfos
                $sprintInfos['hours'] += $taskArray['remaining_hour'];
            }
        }
        
        return array(
            'project'                   => $sprint->getProjectRelease()->getProject(),
            'sprint'                    => $sprint,
            'storyAndTasksByStatus'     => $storyAndTasksByStatus,
            'sprintInfos'               => $sprintInfos,
        );
    }

    /**
     * Displays a form to create a new Sprint entity.
     *
     * @Route("/{id}/new/{story}", name="sprint_new")
     * @Template()
     */
    public function newAction($id, $story)
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
        if (!$member or !in_array($member->getRole()->getId(), array(1, 3))) {
            throw new AccessDeniedException();
        }
        
        // Check if there is already a current sprint
        if ($em->getRepository('NeblionScrumBundle:Sprint')->getCurrentForProject($project->getId())) {
            // Set flash message
            $this->get('session')->setFlash('error', 'There is already a current sprint for this project!');
            return $this->redirect($this->generateUrl('sprint_list', array('id' => $project->getId())));
        }
        
        // Load the last story for sprint
        $lastStory = $em->getRepository('NeblionScrumBundle:Story')->find($story);
        if (!$lastStory) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        // Check if this story belongs to project
        if ($lastStory->getProject() != $project) {
            // Set flash message
            $this->get('session')->setFlash('error', 'Story was not belong to this project!');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }
        
        // Load the current release
        $projectRelease = $em->getRepository('NeblionScrumBundle:ProjectRelease')->getCurrentForProject($project->getId());
        // If there is no current release, create a default one
        if (!$projectRelease) {
            $projectRelease = new \Neblion\ScrumBundle\Entity\ProjectRelease();
            $projectRelease->setName('Default');
        }
        
        $stories = $em->getRepository('NeblionScrumBundle:Story')
                ->getBeforePositionForProject($project->getId(), $lastStory->getPosition());
        $estimate = 0;
        foreach ($stories as $story) {
            if ($story->getEstimate() == 0) {
                // Set flash message
                $this->get('session')->setFlash('error', 'You could not start this sprint, a story was not estimated!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
            $estimate += $story->getEstimate();
        }
        
        // Look for sprint date
        $start = $em->getRepository('NeblionScrumBundle:Sprint')
                ->getStartOfNextSprint($project->getId(), $this->container->getParameter('sprint_duration'));
        $end = new \DateTime($start->format('Y-m-d'));
        $end->modify('+' . $this->container->getParameter('sprint_duration') . ' day');
        
        $entity = new Sprint();
        $entity->setProjectRelease($projectRelease);
        $entity->setStart($start);
        $entity->setEnd($end);
        $entity->setVelocity(0);
        $form   = $this->createForm(new SprintType(), $entity);
        
        return array(
            'project'   => $project,
            'entity'    => $entity,
            'form'      => $form->createView(),
            'estimate'  => $estimate,
            'count'     => count($stories),
            'lastStory' => $lastStory->getId(),
        );
    }

    /**
     * Creates a new Sprint entity.
     *
     * @Route("/{id}/create/{story}", name="sprint_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Sprint:new.html.twig")
     */
    public function createAction($id, $story)
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
            throw new AccessDeniedException();
        }
        
        // Check if there is already a current sprint
        if ($em->getRepository('NeblionScrumBundle:Sprint')->getCurrentForProject($project->getId())) {
            // Set flash message
            $this->get('session')->setFlash('error', 'There is already a current sprint for this project!');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }
        
        // Load the last story for sprint
        $lastStory = $em->getRepository('NeblionScrumBundle:Story')->find($story);
        if (!$lastStory) {
            throw $this->createNotFoundException('Unable to find Story entity.');
        }
        // Check if this story belongs to project
        if ($lastStory->getProject() != $project) {
            // Set flash message
            $this->get('session')->setFlash('error', 'Story was not belong to this project!');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }
        
        // Load default sprint status
        $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        // Load the current release
        $projectRelease = $em->getRepository('NeblionScrumBundle:ProjectRelease')->getCurrentForProject($project->getId());
        // If there is no current release, create a default one
        if (!$projectRelease) {
            // Load in progress status
            $inProgress = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
            
            $projectRelease = new \Neblion\ScrumBundle\Entity\ProjectRelease();
            $projectRelease->setName('Default');
            $projectRelease->setDescription('Default release');
            $projectRelease->setProject($project);
            $projectRelease->setStatus($inProgress);
            $projectRelease->setStart(new \DateTime());
            $em->persist($projectRelease);
        }
        
        $stories = $em->getRepository('NeblionScrumBundle:Story')
                ->getBeforePositionForProject($project->getId(), $lastStory->getPosition());
        $estimate = 0;
        foreach ($stories as $story) {
            if ($story->getEstimate() == 0) {
                // Set flash message
                $this->get('session')->setFlash('error', 'You could not start this sprint, a story was not estimated!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
            $estimate += $story->getEstimate();
        }
        
        // Look for sprint date
        $start = $em->getRepository('NeblionScrumBundle:Sprint')
                ->getStartOfNextSprint($project->getId(), $this->container->getParameter('sprint_duration'));
        $end = new \DateTime($start->format('Y-m-d'));
        $end->modify('+' . $this->container->getParameter('sprint_duration') . ' day');
        
        $entity = new Sprint();
        $entity->setStatus($status);
        $entity->setProjectRelease($projectRelease);
        $entity->setStart($start);
        $entity->setEnd($end);
        $entity->setVelocity(0);
        $form   = $this->createForm(new SprintType(), $entity);
        
        $request = $this->getRequest();
        $form->bindRequest($request);

        if ($form->isValid()) {
            
            $em->persist($entity);
            
            // Affect story to this new sprint
            foreach ($stories as $story) {
                $story->setSprint($entity);
            }
            
            // update sort order for the backlog stories
            $backlogStories = $em->getRepository('NeblionScrumBundle:Story')
                ->getAfterPositionForProject($project->getId(), $lastStory->getPosition(), false);
            $position = 1;
            foreach ($backlogStories as $story) {
                $story->setPosition($position);
                $position++;
            }
            
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Sprint was created successfully!');
            return $this->redirect($this->generateUrl('sprint_list', array('id' => $project->getId())));
        }

        return array(
            'project'   => $project,
            'entity'    => $entity,
            'form'      => $form->createView(),
            'estimate'  => $estimate,
            'count'     => count($stories),
            'lastStory' => $lastStory->getId(),
        );
    }

    /**
     * Displays a form to edit an existing Sprint entity.
     *
     * @Route("/{id}/edit", name="sprint_edit")
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
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->find($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }

        $editForm = $this->createForm(new SprintType(), $sprint);

        return array(
            'project'   => $project,
            'sprint'    => $sprint,
            'form'      => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Sprint entity.
     * 
     * 
     *
     * @Route("/{id}/update", name="sprint_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Sprint:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        $start = $sprint->getStart();
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !in_array($member->getRole()->getId(), array(1, 2))) {
            throw new AccessDeniedException();
        }

        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->find($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }

        $editForm   = $this->createForm(new SprintType(), $sprint);
        $request = $this->getRequest();
        $editForm->bindRequest($request);
        if ($start != $sprint->getStart() and $sprint->getStatus()->getId() != 1) {
            $editForm->addError(new FormError('You can update start date if the sprint is in progress !'));
        }
        
        if ($editForm->isValid()) {
            
            $em->persist($sprint);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Sprint was updated with success!');
            return $this->redirect($this->generateUrl('sprint_list', array('id' => $project->getId())));
        }

        return array(
            'project'   => $project,
            'sprint'    => $sprint,
            'form'      => $editForm->createView(),
        );
    }
    
    /**
     * Start an existing Sprint entity.
     *
     * Starting a sprint consist to update his status to In progress.
     * Sprint have to be in state ToDO.
     * Each stories's sprint must have at least one task.
     * Only admin or Scrumaster could start a sprint.
     * You can no start a sprint if another on is in progress.
     * You can not start a sprint if start date is later than today 
     * 
     * @Route("/{id}/start", name="sprint_start")
     * @Template()
     */
    public function startAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        // If status is different than ToDo, you can start this sprint
        if ($sprint->getStatus()->getId() != 1) {
            $this->get('session')->setFlash('error', 'This sprint is not in ToDo status, so you can not start it !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        }
        
        // Check start date (can be later than today)
        if ($sprint->getStart() > new \DateTime()) {
            $this->get('session')->setFlash('error', 'You can not start this sprint, start date is later than today !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        // Check if user is an admin or scrumaster
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() != 2) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        // Check if another sprint is in progress
        $currentSprint = $em->getRepository('NeblionScrumBundle:Sprint')->getCurrentForProject($project->getId());
        if (!empty($currentSprint)) {
            $this->get('session')->setFlash('error', 'A sprint is still in progress, you can not start this one!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        }
        
        // Check if each stories have at least one task
        $stories = $em->getRepository('NeblionScrumBundle:Story')
                ->getStoriesWithoutTaskForSprint($sprint->getId());
        if (!empty($stories)) {
            $this->get('session')->setFlash('error', 'There is at least one story with no task!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        }
        
        // Load in progress sprint status
        $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
        $sprint->setStatus($status);
        
        $em->flush();
        
        // Set flash message
        $this->get('session')->setFlash('success', 'Sprint was started with success!');
        return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        
        return array(
            'project'   => $project,
            'sprint'    => $sprint,
        );
    }
    
    /**
     * Close an existing Sprint entity.
     *
     * @Route("/{id}/close", name="sprint_close")
     * @Template()
     */
    public function closeAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        // Check if user is an admin or scrumaster
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() != 2) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        $velocity = $em->getRepository('NeblionScrumBundle:Story')
                ->getVelocityForSprint($sprint->getId());
        
        // Get velocity infos
        $stories = $sprint->getStories();
        $currentVelocity = $maxVelocity = $storiesDone = 0;
        foreach ($stories as $story) {
            if ($story->getStatus()->getId() == 3) {
                $storiesDone++;
                $currentVelocity += $story->getEstimate();
                $maxVelocity += $story->getEstimate();
            } else {
                $maxVelocity += $story->getEstimate();
            }
        }
        
        $form = $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                // Update status of the sprint
                // Load status Done
                // Load default sprint status
                $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(3);
                // Load the final velocity for this sprint
                $velocity = $em->getRepository('NeblionScrumBundle:Story')
                        ->getVelocityForSprint($sprint->getId());
                $sprint->setVelocity($velocity);
                $sprint->setStatus($status);
                
                $em->flush();
            }

            // Set flash message
            $this->get('session')->setFlash('success', 'Sprint was closed with success!');
            return $this->redirect($this->generateUrl('sprint_list', array('id' => $project->getId())));
        }
        
        return array(
            'project'           => $project,
            'sprint'            => $sprint,
            'form'              => $form->createView(),
            'currentVelocity'   => $currentVelocity,
            'maxVelocity'       => $maxVelocity,
            'storiesDone'       => $storiesDone,
            'storiesTotal'      => count($stories),
        );
        
    }

    /**
     * Deletes a Sprint entity.
     * 
     * There are tow options when you want to deletet a sprint.
     * First you could delete a sprint and all items associated to it.
     * Second you could delete a sprint and conserve story associted to it, 
     * the stories return in the backog, if you have defined tasks in this stories,
     * tasks will be delete.
     * Only administrator and scrumaster could delete a sprint.
     * 
     * @Route("/{id}/delete", name="sprint_delete")
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
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->find($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project and if he has privilege
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !$member->getRole()->getId() != 1) {
            if (!$member->getAdmin()) {
                throw new AccessDeniedException();
            }
        }
        
        /*
        echo '<pre>';
        print_r($backlog);
        echo '</pre>';
         */      
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $deleteStories = $request->request->get($form->getName() . '[stories]', false, true);
                
                if (!$deleteStories) {
                    $stories = $em->getRepository('NeblionScrumBundle:Story')->getStoriesForSprint($sprint->getId());
                    $count = count($stories);
                    $backlogStories = $em->getRepository('NeblionScrumBundle:Story')->getBacklogStories($project);
                    
                    // Re-Affect stories to the backlog
                    foreach ($stories as $story) {
                        $story->setSprint(null);
                    }
                    
                    // Update story position in backlog
                    foreach ($backlogStories as $story) {
                        $count++;
                        $story->setPosition($count);
                    }
                    
                    
                }
                //echo 'stories: ' . $stories;
                $em->remove($sprint);
                $em->flush();
            }

            // Set flash message
            $this->get('session')->setFlash('success', 'Sprint was deleted with success!');
            return $this->redirect($this->generateUrl('sprint_list', array('id' => $project->getId())));
        }

        return array(
            'project'   => $project,
            'sprint'    => $sprint,
            'form'      => $form->createView(),
        );
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->add('stories', 'checkbox', array(
                'label'   => 'Delete stories ?',
                'required'  => false,
            ))
            ->getForm()
        ;
    }
    
    /**
     * Reports for Sprint.
     *
     * @Route("/{id}/report", name="sprint_report")
     * @Template()
     */
    public function reportAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project and if he has privilege
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Initialize end date
        $end    = new \DateTime($sprint->getEnd()->format('Y-m-d'));
        $today  = new \DateTime();
        if ($today < $end) {
            $end = $today;
        }
        
        // Get initial Hours of task
        $tasks = $em->getRepository('NeblionScrumBundle:Task')->getForSprint($sprint->getId());
        $initialHours = array();
        foreach ($tasks as $task) {
            $initialHours[$task['id']] = $task['hour'];
        }
        $lastHours = $initialHours;
        
        // Get hours
        $hours = $em->getRepository('NeblionScrumBundle:Hour')->getBurndownHours($sprint->getId());
        $dateHours = array();
        foreach ($hours as $element) {
            $dateHours[$element['date']->format('Y-m-d')][$element['task']] = $element['hour'];
        }
        
        // Init start day of burndown chart at start+1 day so we are sure that
        // the firstday has initial hours
        $burndown = array();
        $day = new \DateTime($sprint->getStart()->format('Y-m-d'));
        $day->modify("+1 day");
        // Set initialHours for the firstday
        $burndown[$sprint->getStart()->format('Y-m-d')]['cumul'] = array_sum($initialHours); 
        while ($day <= $end) {
            $burndown[$day->format('Y-m-d')] = array();
            
            foreach ($tasks as $task) {
                if (isset($dateHours[$day->format('Y-m-d')][$task['id']])) {
                    $burndown[$day->format('Y-m-d')][$task['id']] = $dateHours[$day->format('Y-m-d')][$task['id']];
                    $lastHours[$task['id']] = $dateHours[$day->format('Y-m-d')][$task['id']];
                } else {
                    $burndown[$day->format('Y-m-d')][$task['id']] = $lastHours[$task['id']];
                }
            }
            
            $burndown[$day->format('Y-m-d')]['cumul'] = array_sum($burndown[$day->format('Y-m-d')]);
            
            $day->modify('+1 day');
        }
        
        // Create string
        $strHours = '';
        foreach ($burndown as $date => $el) {
            if (!empty($strHours)) {
                $strHours .= ',';
            }
            $strHours .= '[\'' . $date . '\',' . $el['cumul'] . ']';
        }
        
        // Create string for Yaxis
        $estimateInitialHours = array_sum($initialHours);
        $pas = floor($estimateInitialHours / 10);
        $maxY = ($pas - ($estimateInitialHours % $pas)) + $estimateInitialHours;
        $i = 0;
        $strTickY = '[';
        while ($i <= $maxY) {
            if ($strTickY != '[') {
                $strTickY .= ',';
            }
            $strTickY .= $i;
            $i = $i + $pas;
        }
        $strTickY .= ']';
         
        return array(
            'project'               => $sprint->getProjectRelease()->getProject(),
            'sprint'                => $sprint,
            'estimateInitialHours'  => $estimateInitialHours,
            'strHours'              => $strHours,
            'strTickY'              => $strTickY,
        );
    }
}
