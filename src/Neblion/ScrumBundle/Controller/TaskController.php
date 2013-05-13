<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Task;
use Neblion\ScrumBundle\Form\TaskType;
use Neblion\ScrumBundle\Form\TaskEditType;
use Neblion\ScrumBundle\Form\HourType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\Query;

/**
 * Task controller.
 *
 * @Route("/task")
 */
class TaskController extends Controller
{
    /**
     * Displays a form to create a new Task entity.
     *
     * @Route("/{id}/new", name="task_new")
     * @Template()
     * @param integer $id Story id
     */
    public function newAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
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
        if (!$member or $member->getRole()->getId() == 4) {
            throw new AccessDeniedException();
        }
        
        $sprint = $story->getSprint();
        if (!$sprint) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You could not add a task to this story, it was not included in a sprint!');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }
        if ($sprint->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You could not add a task to this story, the associated sprint is done !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        }
        
        // Load initial status
        $status = $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        $success = false;
        $entity = new Task();
        $entity->setStory($story);
        $entity->setStatus($status);
        $form   = $this->createForm(new TaskType(), $entity);
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:new.html.twig', array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $story,
                'entity'    => $entity,
                'form'      => $form->createView(),
                'success'   => $success,
            ));
        } else {
            return array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $story,
                'entity'    => $entity,
                'form'      => $form->createView()
            );
        }
    }

    /**
     * Creates a new Task entity.
     *
     * @Route("/{id}/create", name="task_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Task:new.html.twig")
     * @param integer $id Story id
     */
    public function createAction($id)
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
        if (!$member or $member->getRole()->getId() == 4) {
            throw new AccessDeniedException();
        }
        
        $sprint = $story->getSprint();
        if (!$sprint) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You could not add a task to this story, it was not included in a sprint!');
            return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
        }
        
        // Load initial status
        $status = $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        $success = false;
        $entity  = new Task();
        $entity->setStory($story);
        $entity->setStatus($status);
        $request = $this->getRequest();
        $form    = $this->createForm(new TaskType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            
            // Add initial hour record
            $hour = new \Neblion\ScrumBundle\Entity\Hour();
            $hour->setTask($entity);
            $hour->setDate(new \DateTime());
            $hour->setHour($entity->getHour());
            $em->persist($hour);
            
            // Update story's status if it was done, update to in progress
            if ($story->getStatus()->getId() == 3) {
                // Load initial status
                $status = $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
                $story->setStatus($status);
            }
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'created task', 
                    $this->generateUrl('sprint_show', array('id' => $sprint->getId())), 
                    'Sprint #' . $sprint->getId() . ' ' . $entity->getName());
            
            $em->flush();

            $success = true;

            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('success', 'Task was successfully created!');
                return $this->redirect($this->generateUrl('sprint_show', array('id' => $story->getSprint()->getId())));
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:new.html.twig', array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $story,
                'entity'    => $entity,
                'form'      => $form->createView(),
                'success'   => $success,
            ));
        } else {
            return array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $story,
                'entity'    => $entity,
                'form'      => $form->createView()
            );
        }
    }
    
    /**
     * Displays a form to create a storyless new task
     *
     * @Route("/{id}/newstoryless", name="task_new_storyless")
     * @Template()
     * @param integer $id Sprint id
     */
    public function newStorylessAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id, Query::HYDRATE_OBJECT);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() == 4) {
            throw new AccessDeniedException();
        }

        // Check sprint status
        if ($sprint->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You could not add a task to this story, the associated sprint is done !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $sprint->getId())));
        }
        
        // Load initial status
        $status = $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        $success = false;
        $entity = new Task();
        //$entity->setStory($story);
        $entity->setStatus($status);
        $form   = $this->createForm(new TaskType(), $entity);
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:newStoryless.html.twig', array(
                'project'   => $project,
                'sprint'    => $sprint,
                //'story'     => $story,
                'entity'    => $entity,
                'form'      => $form->createView(),
                'success'   => $success,
            ));
        } else {
            return array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $story,
                'entity'    => $entity,
                'form'      => $form->createView()
            );
        }
    }
    
    /**
     * Creates a new Task entity.
     *
     * @Route("/{id}/createstoryless", name="task_create_storyless")
     * @Method("post")
     * @Template("NeblionScrumBundle:Task:newStoryless.html.twig")
     * @param integer $id Sprint id
     */
    public function createStorylessAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $sprint = $em->getRepository('NeblionScrumBundle:Sprint')->load($id, Query::HYDRATE_OBJECT);
        if (!$sprint) {
            throw $this->createNotFoundException('Unable to find Sprint entity.');
        }
        $project = $sprint->getProjectRelease()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() == 4) {
            throw new AccessDeniedException();
        }
        
        // Load initial status
        $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        $new = false;
        
        // Load the storyless story for this sprint
        $storyless = $em->getRepository('NeblionScrumBundle:Story')->getStoryLessForSprint($sprint);
        // Create the storyless story
        if (!$storyless) {
            // Load storyType 'Story less'
            $type = $em->getRepository('NeblionScrumBundle:StoryType')->find(4);
            
            $storyless = new \Neblion\ScrumBundle\Entity\Story();
            $storyless->setProject($project);
            $storyless->setSprint($sprint);
            $storyless->setStatus($status);
            $storyless->setType($type);
            $storyless->setName($type->getName());
            $storyless->setDescription($type->getName());
            $storyless->setPosition(0);
            $storyless->setEstimate(0);
            $em->persist($storyless);
            $new = true;
        }
        
        $success = false;
        $entity  = new Task();
        $entity->setStory($storyless);
        $entity->setStatus($status);
        $request = $this->getRequest();
        $form    = $this->createForm(new TaskType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            
            // Add initial hour record
            $hour = new \Neblion\ScrumBundle\Entity\Hour();
            $hour->setTask($entity);
            $hour->setDate(new \DateTime());
            $hour->setHour($entity->getHour());
            $em->persist($hour);
            
            // Update story's status if it was done, update to in progress
            if ($storyless->getStatus()->getId() == 3) {
                // Load initial status
                $status = $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
                $storyless->setStatus($status);
            }
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'created storyless task', 
                    $this->generateUrl('sprint_show', array('id' => $sprint->getId())), 
                    'Sprint #' . $sprint->getId() . ' ' . $entity->getName());
            
            $em->flush();

            $success = true;

            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('success', 'Task was successfully created!');
                return $this->redirect($this->generateUrl('sprint_show', array('id' => $story->getSprint()->getId())));
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:newStoryless.html.twig', array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $storyless,
                'entity'    => $entity,
                'form'      => $form->createView(),
                'success'   => $success,
                'new'       => $new,
            ));
        } else {
            return array(
                'project'   => $project,
                'sprint'    => $sprint,
                'story'     => $storyless,
                'entity'    => $entity,
                'form'      => $form->createView()
            );
        }
    }

    /**
     * Displays a form to edit an existing Task entity.
     * 
     * Task edition
     *
     * @Route("/{id}/edit", name="task_edit")
     * @Template()
     * TODO: factorize form Task
     */
    public function editAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Check if task is done
        if ($task->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Task is done, you could not edit it!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if associated sprint is closed
        if ($task->getStory()->getSprint()->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Associated sprint is done, you could not edit this task !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // if task is in progress, you could not edit estimate hours
        if ($task->getStatus()->getId() == 2) {
            // Get the last hour
            $last = $em->getRepository('NeblionScrumBundle:Hour')->getLastForTask($task->getId());
            if (empty($last)) {
                $remainingHours = $task->getHour();
            } else {
                $remainingHours = $last->getHour();
            }
            $editForm   = $this->createForm(new TaskType(), $task, array('remaining' => true, 'hours' => $remainingHours));
        } else {
            $editForm   = $this->createForm(new TaskType(), $task);
        }
        
        return array(
            'project'   => $project,
            'task'      => $task,
            'form'      => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Task entity.
     *
     * @Route("/{id}/update", name="task_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Task:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Check if task is done
        if ($task->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Task is done, you could not edit it!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if associated sprint is closed
        if ($task->getStory()->getSprint()->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Associated sprint is done, you could not edit this task !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }

        // if task is in progress, you could not edit estimate hours
        if ($task->getStatus()->getId() == 2) {
            // Get the last hour
            $last = $em->getRepository('NeblionScrumBundle:Hour')->getLastForTask($task->getId());
            if (empty($last)) {
                $remainingHours = $task->getHour();
            } else {
                $remainingHours = $last->getHour();
            }
            $editForm   = $this->createForm(new TaskType(), $task, array('remaining' => true, 'hours' => $remainingHours));
        } else {
            $editForm   = $this->createForm(new TaskType(), $task);
        }

        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $remainingHours = $request->request->get($editForm->getName() . '[remaining_hour]', $task->getHour(), true);
            if ($task->getStatus()->getId() != 3) {
                // Manage Hour
                // Get hour
                $hour = $em->getRepository('NeblionScrumBundle:Hour')->getForTaskAndDate($task->getId(), date('Y-m-d'));
                if (!$hour) {
                    $hour  = new \Neblion\ScrumBundle\Entity\Hour();
                    $hour->setTask($task);
                    $hour->setDate(new \DateTime());
                    $hour->setHour($remainingHours);
                    $em->persist($hour);
                } else {
                    $hour->setHour($remainingHours);
                }
            }
            
            // store activity            
            $this->get('scrum_activity')->add($project, $user, 'updated task', 
                    $this->generateUrl('task_edit', array('id' => $task->getId())), 
                    'Task #' . $task->getId());
            
            $em->flush();

            // Set flash message
            $this->get('session')->getFlashBag()->add('success', 'Task was successfully updated!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }

        return array(
            'project'   => $project,
            'task'      => $task,
            'form'      => $editForm->createView(),
        );
    }
    
    /**
     * Edit remaining hours of an existing Task entity.
     * 
     * Only the member who take the task could be edit the remaining hours.
     * If remaining hours is setting to 0 so the task is setting to done.
     * Task and sprint status have to be in progress.
     *
     * @Route("/{id}/hours", name="task_hours")
     * @Template()
     * @param integer $id Task id
     */
    public function hoursAction($id)
    {
        $error = false;

        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // check if associated sprint is in progress
        if ($task->getStory()->getSprint()->getStatus()->getId() != 2) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                $error = 'The associated sprint is not in progress !';
            } else {
                // Set flash message
                $this->get('session')->getFlashBag()->add('error', 'Associtaed sprint is not assign to you, you could not edit it!');
                return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
            }
        }
        
        // Check if task is assign to the current user
        if ($task->getMember() and $task->getMember()->getAccount()->getId() != $user->getId()) {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('error', 'Task is not assign to you, you could not edit it!');
                return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
            } else {
                $error = 'Task is not assign to you, you could not edit it!';
            }
        }
        
        // Check if task is done
        if ($task->getStatus()->getId() != 2) {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('error', 'Task is not in progress, you could not edit their hours !');
                return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
            } else {
                $error = 'Task is not in progress, you could not edit their hours !';
            }
        }
        
        $hour = $em->getRepository('NeblionScrumBundle:Hour')->getLastForTask($task->getId());
        
        $success        = false;
        $remainingHours = false;
        $form           = $this->createForm(new HourType(), $hour);
        $request        = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $remainingHours = $hour->getHour();
                // if the Hour object has not the today date so create a new Hour
                if ($hour->getDate()->format('Y-m-d') != date('Y-m-d')) {
                    // Re-init last hour record to this previous value
                    $em->refresh($hour);
                    
                    $hourNew  = new \Neblion\ScrumBundle\Entity\Hour();
                    $hourNew->setTask($task);
                    $hourNew->setDate(new \DateTime());
                    $hourNew->setHour($remainingHours);
                    $em->persist($hourNew);
                }
                
                // Update task status if hour = 0
                if ($remainingHours == 0) {
                    // Load done status
                    $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(3);
                    $task->setStatus($status);
                    
                    // Check if all the task of the story are done
                    $all = true;
                    foreach ($task->getStory()->getTasks() as $tk) {
                        if ($tk->getStatus()->getId() != 3) {
                            $all = false;
                            break;
                        }
                    }
                    // If all task are done, set story status to done
                    if ($all) {
                        $task->getStory()->setStatus($status);
                    }
                }
                
                // store activity            
                $this->get('scrum_activity')->add($project, $user, "updated task's hours", 
                    $this->generateUrl('task_edit', array('id' => $task->getId())), 
                    'Task #' . $task->getId());
                
                $em->flush();
                
                $success = true;

                if (!$this->getRequest()->isXmlHttpRequest()) {
                    // Set flash message
                    $this->get('session')->getFlashBag()->add('success', 'Task was updated with success!');
                    return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
                }
            }
        }
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:hours.html.twig', array(
                //'project'           => $project,
                'task'              => $task,
                'hour'              => $hour,
                'form'              => $form->createView(),
                'success'           => $success,
                'remainingHours'    => $remainingHours,
                'error'             => $error,
            ));   
        } else {
            return array(
                'project'   => $project,
                'task'      => $task,
                'hour'      => $hour,
                'form'      => $form->createView(),
            );
        }
    }
    
    /**
     * Take a task
     * 
     * Associate a task to the current member
     *
     * @Route("/{id}/take", name="task_take")
     * @Template()
     */
    public function takeAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() == 4) {
            throw new AccessDeniedException();
        }
        
        // Check if the associated sprint is closed
        if ($task->getStory()->getSprint()->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You can not take task, the sprint is closed !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
  
        // Check if the sprint is started
        if ($task->getStory()->getSprint()->getStatus()->getId() == 1) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You can not take task, the sprint is not in progress !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        if ($task->getMember() and $task->getMember()->getId() == $user->getId()) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'This task is already assign to you!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        if ($task->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You can not take this task, it was done!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Load status In Progress
        $status = $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
        
        // If current status of task is ToDo
        if ($task->getStatus()->getId() == 1) {
            // Manage Hour record
            $hour = $em->getRepository('NeblionScrumBundle:Hour')->getForTaskAndDate($task->getId(), date('Y-m-d'));
            if (!$hour) {
                $hour = new \Neblion\ScrumBundle\Entity\Hour();
                $hour->setTask($task);
                $hour->setDate(new \DateTime());
                $hour->setHour($task->getHour());
                $em->persist($hour);
            }
            
            // Check if the storyis already in progress
            if ($task->getStory()->getStatus()->getId() == 1) {
                // Set in progress
                $task->getStory()->setStatus($status);
            }
        }
        
        // When take a task, task automatically become in progress
        $task->setStatus($status);
        $task->setMember($member);
        
        // store activity            
        $this->get('scrum_activity')->add($project, $user, 'took task', 
                    $this->generateUrl('task_edit', array('id' => $task->getId())), 
                    'Task #' . $task->getId());
        
        $em->flush();

        // Set flash message
        $this->get('session')->getFlashBag()->add('success', 'Task was successfully updated!');
        return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
    }
    
    /**
     * Set status to In Progress
     * 
     * To set a task in progress:
     * - sprint must be in progress (not closed and not to do)
     * 
     *
     * @Route("/{id}/setInProgress", name="task_set_inprogress")
     * @Template()
     */
    public function setInProgressAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Check if the associated sprint is closed
        if ($task->getStory()->getSprint()->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You can not update this task, the sprint is closed !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if the associated sprint is in progress
        if ($task->getStory()->getSprint()->getStatus()->getId() == 1) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You can not set this task in progress, the sprint is not started !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if the task is assigned
        if (!$task->getMember()) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Unable to change status, task is not assign!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if the task is own by the user or if user is ADMIN or SM
        if ($task->getMember()->getAccount()->getId() != $user->getId() and !in_array($member->getRole()->getId(), array(1, 3))) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Unable to change status, you do not have sufficient rights !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if task was already in progress
        if ($task->getStatus()->getId() == 2) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Task was already in progress!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Manage Hour record
        $hour = $em->getRepository('NeblionScrumBundle:Hour')->getForTaskAndDate($task->getId(), date('Y-m-d'));
        if (!$hour) {
            $hour = new \Neblion\ScrumBundle\Entity\Hour();
            $hour->setTask($task);
            $hour->setDate(new \DateTime());
            $hour->setHour(0);
            $em->persist($hour);
        }
        $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
        $task->setStatus($status);
        
        // Update story's status if it was done, update to In Progress
        if ($task->getStory()->getStatus()->getId() == 3 or 
                $task->getStory()->getStatus()->getId() == 1) {
            $story = $task->getStory();
            $story->setStatus($status);
        }
        
        // store activity            
        $this->get('scrum_activity')->add($project, $user, 'set task in progress', 
                    $this->generateUrl('task_edit', array('id' => $task->getId())), 
                    'Task #' . $task->getId());
        
        $em->flush();

        // Set flash message
        $this->get('session')->getFlashBag()->add('success', 'Task was successfully updated!');
        return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
    }
    
    /**
     * Set status to Done
     * 
     * Change task status to Done.
     * Only a task in progress could become done.
     * All members except member with role 'Member' could set a task to done
     * When you set a task to done, task's hours remaining is setting to 0.
     *
     * @Route("/{id}/setDone", name="task_set_done")
     * @Template()
     * @param integer id Task id
     */
    public function setDoneAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or $member->getRole()->getId() == 4) {
            throw new AccessDeniedException();
        }
        
        // Check if the associated sprint is closed
        if ($task->getStory()->getSprint()->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You can not update this task, the sprint is closed !');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if the task is assigned
        if (!$task->getMember()) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Unable to change status, task is not assign!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if task was already done
        if ($task->getStatus()->getId() == 3) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Task was already done!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Manage Hour record
        $hour = $em->getRepository('NeblionScrumBundle:Hour')->getForTaskAndDate($task->getId(), date('Y-m-d'));
        if (!$hour) {
            $hour = new \Neblion\ScrumBundle\Entity\Hour();
            $hour->setTask($task);
            $hour->setDate(new \DateTime());
            $hour->setHour(0);
            $em->persist($hour);
        } else {
            $hour->setHour(0);
        }
        // Change status of task
        $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(3);
        $task->setStatus($status);
        
        // Check if all story's task is done so update the story's status to done
        $allDone = true;
        $tasks = $em->getRepository('NeblionScrumBundle:Task')->findByStory($task->getStory()->getId());
        foreach ($tasks as $verifTask) {
            if ($verifTask->getStatus()->getId() != 3) {
                $allDone = false;
                break;
            }
        }
        // Change story's status to Done
        if ($allDone) {
            $story = $task->getStory();
            $story->setStatus($status);
        }
        
        // store activity            
        $this->get('scrum_activity')->add($project, $user, 'set task done', 
                    $this->generateUrl('task_edit', array('id' => $task->getId())), 
                    'Task #' . $task->getId());
        
        $em->flush();

        // Set flash message
        $this->get('session')->getFlashBag()->add('success', 'Task was successfully updated!');
        return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
    }
    
    /**
     * Deletes a Task entity.
     *
     * @Route("/{id}/delete", name="task_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Load the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('NeblionScrumBundle:Task')->load($id);
        if (!$task) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }
        $project = $task->getStory()->getProject();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Check if the task is own by the user or if user is ADMIN or SM
        if (!in_array($member->getRole()->getId(), array(1, 3))) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'You have not sufficient privilege to delete this task!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        // Check if task could be deleted
        // Check status
        if ($task->getStatus()->getId() != 1) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('error', 'Task could not be deleted!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                // Check if all task is done for story, so update story's status to Done
                $allDone = true;
                $tasks = $em->getRepository('NeblionScrumBundle:Task')->findByStory($task->getStory()->getId());
                foreach ($tasks as $verifTask) {
                    if ($verifTask->getId() != $task->getId()) {
                        if ($verifTask->getStatus()->getId() != 3) {
                            $allDone = false;
                            break;
                        }
                    }
                }
                // Change story's status to Done
                if ($allDone) {
                    // Load the done status
                    $status = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(3);
                    $story = $task->getStory();
                    $story->setStatus($status);
                }
                
                $em->remove($task);
                
                // store activity            
                $this->get('scrum_activity')->add($project, $user, 'deleted task', 
                    $this->generateUrl('sprint_show', array('id' => $task->getId())), 
                    'Task #' . $task->getId());
                
                $em->flush();
            }

            // Set flash message
            $this->get('session')->getFlashBag()->add('success', 'Task was deleted with success!');
            return $this->redirect($this->generateUrl('sprint_show', array('id' => $task->getStory()->getSprint()->getId())));
        }
        
        return array(
            'project'   => $project,
            'task'      => $task,
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
     * Load Todo Task
     *
     * @Route("/{id}/todo", name="task_todo")
     * @Template()
     */
    public function loadToDoAction($id)
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
        $sprint = $story->getSprint();
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Load tasks
        $tasks = $em->getRepository('NeblionScrumBundle:Task')->loadToDo($story->getId());
        
        $sprint = $story->getSprint();
        
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
                
                $resultTasks[] = $taskArray;
        }
        
        return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Task/Ajax:loadToDo.html.twig', array(
            'tasks' => $resultTasks, 'sprint' => $sprint, 'story' => $story
        ));
    }
    
    
}
