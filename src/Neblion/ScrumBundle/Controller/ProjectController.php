<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Project;
use Neblion\ScrumBundle\Form\ProjectType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
                ->getStartOfNextSprint($project->getId(), $this->container->getParameter('sprint_start_day'));
        $endOfNextSprint = new \DateTime($startOfNextSprint->format('Y-m-d'));
        $endOfNextSprint->modify('+' . $this->container->getParameter('sprint_duration') . ' day');
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->container->get('templating')->renderResponse('NeblionScrumBundle:Project/Ajax:backlog.html.twig', array(
                'project'           => $project,
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
        
        $em = $this->getDoctrine()->getEntityManager();

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
        /*
        echo '<pre>';
        print_r($datas);
        echo '</pre>';
        */
        
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
        
        return array(
            'project'       => $project,
            //'currentSprint' => $currentSprint,
            'strDone'   => $strDone,
            'strTotal'   => $strTotal,
            'strXTicks' => $strXTicks,
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
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $entity = new Project();
        $form   = $this->createForm(new ProjectType(), $entity);

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
        $form    = $this->createForm(new ProjectType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            // Get the current user
            $user = $this->get('security.context')->getToken()->getUser();
            
            $em = $this->getDoctrine()->getEntityManager();
                       
            // Create project
            $em->persist($entity);
            // Create team related to project
            $team = new \Neblion\ScrumBundle\Entity\Team();
            $team->setProject($entity);
            $team->setName($entity->getName());
            $em->persist($team);
            
            // Create team member
            // Add current user to new team and set him admin role
            $status = $em->getRepository('NeblionScrumBundle:MemberStatus')->find(2);
            $role   = $em->getRepository('NeblionScrumBundle:Role')->find(1);
            $member = new \Neblion\ScrumBundle\Entity\Member();
            $member->setTeam($team);
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
            
            $em->flush();

            return $this->redirect($this->generateUrl('project_dashboard', array('id' => $entity->getId())));
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
        
        $em = $this->getDoctrine()->getEntityManager();
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $editForm = $this->createForm(new ProjectType(), $project);

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
        
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $editForm   = $this->createForm(new ProjectType(), $entity);
        $request = $this->getRequest();
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Project was successfully updated!');
            return $this->redirect($this->generateUrl('project_dashboard', array('id' => $id)));
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
        
        $em = $this->getDoctrine()->getEntityManager();
        
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
        
        // Check if ther are more than one administrator for this project
        /*
        $admins = $em->getRepository('NeblionScrumBundle:Member')
                ->getAdminsForProject($project->getId());
        */
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->remove($project);
                $em->flush();
        
                // Set flash message
                $this->get('session')->setFlash('success', 'Project was successfully deleted!');
                return $this->redirect($this->generateUrl('neblion_scrum_welcome'));
            }
        }

        return array(
            'project'   => $project,
            //'admins'    => $admins,
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
        
        $em = $this->getDoctrine()->getEntityManager();
        
        // Load project
        $project = $em->getRepository('NeblionScrumBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        // Load backlog stories for check
        $stories = $em->getRepository('NeblionScrumBundle:Story')->getBacklogStories($project);
        $checkStories = array();
        foreach($stories as $story) {
            $checkStories[$story->getId()] = $story->getId();
        }
        /*
        echo '<pre>';
        print_r($checkStories);
        echo '</pre>';
        */
        $storySortOrder = null;
        
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            if ($request->request->has('story-sort-order')) {
                $storySortOrder = $request->request->get('story-sort-order');
            }
            //echo 'storySortOrder: ' . $storySortOrder . '<br />';
            
            if (empty($storySortOrder)) {
                // redirect + flash mesg + log
                // Set flash message
                $this->get('session')->setFlash('notice', 'Noting to sort in the Backlog!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
            
            $tabsort = explode(',', $storySortOrder);
            /*
            echo '<pre>';
            print_r($tabsort);
            echo '</pre>';
            exit;
            */
            foreach ($tabsort as $story_id) {
                if (!array_key_exists($story_id, $checkStories)) {
                    // Detect a story not in backlog !!!
                    // TODO: log + alert
                    // Set flash message
                    $this->get('session')->setFlash('error', 'You try to sort a story who not in the Backlog!');
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
                $this->get('session')->setFlash('success', 'Backlog was successfully sorted!');
                return $this->redirect($this->generateUrl('project_backlog', array('id' => $project->getId())));
            }
        }
    }
}
