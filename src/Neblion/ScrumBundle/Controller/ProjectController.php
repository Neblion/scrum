<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Project;
use Neblion\ScrumBundle\Form\ProjectType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * Project controller.
 *
 * @Route("/project")
 */
class ProjectController extends Controller
{
    
    /**
     * @Route("/list", name="project_list")
     * @Template()
     */
    public function projectsAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        if (!$user->getProfile()) {
            // Set flash message
            $this->get('session')->getFlashBag()->add('notice', 'You have not completed your profile, please complete it!');
            return $this->redirect($this->generateUrl('profile_new'));
        }
        
        // Check if the user has a pending invitation
        if ($em->getRepository('NeblionScrumBundle:Member')->hasPendingInvitation($user)) {
            return $this->redirect($this->generateUrl('member_invitation'));
        }
        
        $projects = $em->getRepository('NeblionScrumBundle:Project')
                ->getListForUser($user->getId());
        
        return array(
            'projects' => $projects,
            'user'      => $user,
        );
    }
    
    /**
     * @Route("/{id}/backlog", name="project_backlog")
     * @Template()
     */
    public function backlogAction($id)
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
        if (!$member) {
            throw new AccessDeniedException();
        }
        
        // Load the current release
        $projectRelease = $em->getRepository('NeblionScrumBundle:ProjectRelease')
                ->getCurrentForProject($project->getId());
        if (!$projectRelease) {
            $projectRelease = new \Neblion\ScrumBundle\Entity\ProjectRelease();
            $projectRelease->setName('Default');
        }
        
        $backlog = $nextSprint = array();
        $nextSprint['stories'] = array();
        $stories = $em->getRepository('NeblionScrumBundle:Story')->getBacklog($project);
        $acceptNextSprint = true;
        $capacity = $estimate = 0;
        
        // Init velocity
        $velocity = $em->getRepository('NeblionScrumBundle:Project')->getVelocity($project->getId());
        if (empty($velocity)) {
            $velocity = $this->container->getParameter('default_velocity');
        }
        
        if (!empty($stories)) {
            foreach ($stories as $story) {
                if ($capacity + $story['estimate'] <= $velocity and $acceptNextSprint 
                        and $story['estimate'] > 0) {
                    $nextSprint['stories'][] = $story;
                    $capacity += $story['estimate'];
                } else {
                    if ($acceptNextSprint) {
                        $acceptNextSprint = False;
                    }
                    $backlog['stories'][] = $story;
                    $estimate += $story['estimate'];
                }
            }
            $nextSprint['estimate'] = $capacity;
            $backlog['estimate'] = $estimate;
        }
        
        $startOfNextSprint = $em->getRepository('NeblionScrumBundle:Sprint')
                ->getStartOfNextSprint($project->getId(), $project->getSprintStartDay());
        $endOfNextSprint = new \DateTime($startOfNextSprint->format('Y-m-d'));
        $endOfNextSprint->modify('+' . $this->container->getParameter('sprint_duration') . ' day');
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Project/Ajax:backlog.html.twig', array(
                'project'           => $project,
                'member'            => $member,
                'projectRelease'    => $projectRelease,
                'nextSprint'        => $nextSprint,
                'backlog'           => $backlog,
                'velocity'          => $velocity,
                'sprintDuration'    => $this->container->getParameter('sprint_duration'),
                'startOfNextSprint' => $startOfNextSprint,
                'endOfNextSprint'   => $endOfNextSprint,
            ));
        } else {
            return array(
                'project'           => $project,
                'member'            => $member,
                'projectRelease'    => $projectRelease,
                'nextSprint'        => $nextSprint,
                'backlog'           => $backlog,
                'velocity'          => $velocity,
                'sprintDuration'    => $this->container->getParameter('sprint_duration'),
                'startOfNextSprint' => $startOfNextSprint,
                'endOfNextSprint'   => $endOfNextSprint,
            );
        }
    }

    /**
     * Finds and displays a Project entity.
     *
     * @Route("/{id}/dashboard", name="project_dashboard")
     * @Template()
     */
    public function dashboardAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();

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
        
        // Get the current sprint
        //$currentSprint = $em->getRepository('NeblionScrumBundle:Sprint')->getCurrentForProject($project->getId());
        
        // Get Data for burn up points / sprint chart
        $datas = $em->getRepository('NeblionScrumBundle:Project')->getDataBurnUpPoints($project);
        
        $strTotal = $strDone = '';
        foreach ($datas['data'] as $sprint => $values) {
            if (!empty($strTotal)) {
                $strTotal .= ',';
                $strDone .= ',';
            }
            $strTotal .= '[\'' . $sprint . '\',' . $values['total'] . ']';
            $strDone .= '[\'' . $sprint . '\',' . $values['done'] . ']';
        }
        $strXTicks = '';
        foreach ($datas['ticks'] as $value => $label) {
            if (!empty($strXTicks)) {
                $strXTicks .= ',';
            }
            $strXTicks .= '[' . $value . ',\'' . $label . '\']';
        }
        
        // Load activities
        $activities = $em->getRepository('NeblionScrumBundle:Activity')
                ->loadForProject($project);
        
        return array(
            'project'       => $project,
            'strDone'       => $strDone,
            'strTotal'      => $strTotal,
            'strXTicks'     => $strXTicks,
            'activities'    => $activities,
        );
    }

    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/new", name="project_new")
     * @Template()
     */
    public function newAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $entity = new Project();
        $form   = $this->createForm(new ProjectType($this->get('translator')), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Project entity.
     *
     * @Route("/create", name="project_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Project:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Project();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProjectType($this->get('translator')), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            // Get the current user
            $user = $this->get('security.context')->getToken()->getUser();
            
            $em = $this->getDoctrine()->getManager();
                       
            // Create project
            $em->persist($entity);
                        
            // Create member
            // Add current user to new team and set him admin role
            $status = $em->getRepository('NeblionScrumBundle:MemberStatus')->find(2);
            $role   = $em->getRepository('NeblionScrumBundle:Role')->find(1);
            $member = new \Neblion\ScrumBundle\Entity\Member();
            $member->setProject($entity);
            $member->setStatus($status);
            $member->setAccount($user);
            $member->setRole($role);
            $member->setAdmin(true);
            $em->persist($member);
            
            // Create a default release
            $releaseStatus = $em->getRepository('NeblionScrumBundle:ProcessStatus')->find(2);
            $release = new \Neblion\ScrumBundle\Entity\ProjectRelease();
            $release->setProject($entity);
            $release->setStatus($releaseStatus);
            $release->setName('Default');
            $release->setDescription('Default release');
            $release->setStart(new \DateTime());
            $em->persist($release);
            
            // Create a default feature
            $feature = new \Neblion\ScrumBundle\Entity\Feature();
            $feature->setProject($entity);
            $feature->setName('Default');
            $feature->setDescription('Default feature');
            $feature->setColor('#ffffff');
            $em->persist($feature);
            
            if ($entity->getIsPublic()) {
                // store activity            
                $this->get('scrum_activity')->add($entity, $user, 'create project ' . $entity->getName(), 
                    $this->generateUrl('project_search') . '?query=' . $entity->getName(),
                    'Project ' . $entity->getName());
            }
            
            $em->flush();

            // Set flash message
            $this->get('session')->getFlashBag()->add('success', 'Project was successfully created!');
            return $this->redirect($this->generateUrl('project_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="project_edit")
     * @Template()
     */
    public function editAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Get the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !$member->getAdmin()) {
            throw new AccessDeniedException();
        }

        $editForm = $this->createForm(new ProjectType($this->get('translator')), $project);

        return array(
            'project'     => $project,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}/update", name="project_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Project:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        // Get the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $entity->getId());
        if (!$member or !$member->getAdmin()) {
            throw new AccessDeniedException();
        }

        $editForm   = $this->createForm(new ProjectType($this->get('translator')), $entity);
        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            
            // store activity            
            $this->get('scrum_activity')->add($entity, $user, 'update project ' . $entity->getName(), 
                    $this->generateUrl('project_dashboard', array('id' => $entity->getId())), 
                    'Project #' . $entity->getId());
            
            $em->flush();

            // Set flash message
            $this->get('session')->getFlashBag()->add('success', 'Project was successfully updated!');
            return $this->redirect($this->generateUrl('project_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a Project entity.
     * 
     * Delete a project will delete all items link to the project, 
     * story, tasks, features, releases, member...
     * 
     * Only an admin could delete a project.
     * If there are more than one administrator a confirmation from 
     * another admin is neccessary for delete project.
     *
     * @Route("/{id}/delete", name="project_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        
        // Get the current user
        $user = $this->get('security.context')->getToken()->getUser();
        
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        // Check if user is really a member of this project and if this user 
        // is an admin user
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !$member->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->remove($project);
                
                /*
                // store activity            
                $this->get('scrum_activity')->add($project, $user, 'remove project', 
                    $this->generateUrl('project_search'), 
                    'Project #' . $project->getId() . ' ' . $project->getName());
                */

                $em->flush();
        
                // Set flash message
                $this->get('session')->getFlashBag()->add('success', 'Project was successfully deleted!');
                return $this->redirect($this->generateUrl('neblion_scrum_welcome'));
            }
        }

        return array(
            'project'   => $project,
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
     * backlogSortOrderAction
     * 
     * @Route("/{id}/backlog/sort/order", name="backlog_sort_order")
     * @Method("post")
     * 
     * @return type
     * @throws AccessDeniedException 
     */
    public function backlogSortOrderAction($id)
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
        
        // Check if user is really a member of this project and if this user 
        // is an admin user
        $member = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$member or !$member->getAdmin()) {
            throw new AccessDeniedException();
        }

        // Load backlog stories for check
        $stories = $em->getRepository('NeblionScrumBundle:Story')->getBacklogStories($project);
        $checkStories = array();
        foreach($stories as $story) {
            $checkStories[$story->getId()] = $story->getId();
        }

        $storySortOrder = null;
        
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            if ($request->request->has('story-sort-order')) {
                $storySortOrder = $request->request->get('story-sort-order');
            }
            
            if (empty($storySortOrder)) {
                // Set flash message
                $this->get('session')->getFlashBag()->add('notice', 'Noting to sort in the Backlog!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
            
            $tabsort = explode(',', $storySortOrder);
            
            foreach ($tabsort as $story_id) {
                if (!array_key_exists($story_id, $checkStories)) {
                    // Detect a story not in backlog !!!
                    // TODO: log + alert
                    // Set flash message
                    $this->get('session')->getFlashBag()->add('error', 'You try to sort a story who not in the Backlog!');
                    return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
                }
            }
                
            for ($i = 0; $i < count($tabsort); $i++) {
                $story = $em->getRepository('NeblionScrumBundle:Story')->find($tabsort[$i]);
                $story->setPosition($i + 1);
            }
            
            $em->flush();
            
            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Project/Ajax:backlogSortOrder.html.twig', array());   
            } else {
                // Set flash message
                $this->get('session')->getFlashBag()->add('success', 'Backlog was successfully sorted!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
        }
    }
    
    /**
     * @Route("/search", name="project_search")
     * @Template()
     */
    public function searchAction()
    {
        $request = $this->getRequest();
        
        $em = $this->getDoctrine()->getManager();
        $searchString = $request->query->get('query');
        $query = $em->getRepository('NeblionScrumBundle:Project')->search($searchString);
        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $page = $request->get('page', 1);
        $pager->setMaxPerPage(5);
        $pager->setCurrentPage($page);
        
        return array(
            'searchString'  => $searchString,
            'pager'         => $pager,
        );
    }
    
    /**
     * @Route("/{id}/activity", name="project_activity")
     * @Template()
     */
    public function activityAction($id)
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
        
        if (!$project->getIsPublic()) {
            // Check if user is really a member of this project
            $member = $em->getRepository('NeblionScrumBundle:Member')
                    ->isMemberOfProject($user->getId(), $project->getId());
            if (!$member) {
                throw new AccessDeniedException();
            }
        }
        
        $request = $this->getRequest();
        
        $query = $em->getRepository('NeblionScrumBundle:Activity')
                ->loadForProject($project, true);
        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $page = $request->get('page', 1);
        $pager->setMaxPerPage(10);
        $pager->setCurrentPage($page);
        
        return array(
            'project'   => $project,
            'pager'     => $pager,
        );
    }
}
