<?php

namespace Neblion\ScrumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Neblion\ScrumBundle\Entity\Member;
use Neblion\ScrumBundle\Form\MemberType;
use Neblion\ScrumBundle\Form\InvitationType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Member controller.
 *
 * @Route("/member")
 */
class MemberController extends Controller
{
    /**
     * Lists member sof a project.
     *
     * @Route("/{id}/list", name="member_list")
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
        
        $admin = false;
        $membersDisabled = array();
        
        if ($member->getAdmin()) {
            $admin = true;
            // Get Members of the team
            $membersDisabled = $em->getRepository('NeblionScrumBundle:Member')
                    ->getTeamMembersNotEnabled($project);
        }

        // Get Members of the team
        $members = $em->getRepository('NeblionScrumBundle:Member')
                ->getTeamMembers($project);
        
        return array(
            'project'           => $project,
            'members'           => $members,
            'membersDisabled'   => $membersDisabled,
            'admin'             => $admin,
        );
    }

    /**
     * Displays a form to create a new Member entity.
     * 
     * Only administrator could be invit/create new member.
     *
     * @Route("/{id}/new", name="member_new")
     * @Template()
     * @param integer $id Project id
     */
    public function newAction($id)
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
        if (!$member or  !$member->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(new InvitationType());
        
        return array(
            'project'   => $project,
            'form'      => $form->createView()
        );
    }

    /**
     * Creates a new Member entity.
     *
     * Create a new member and add him to the team with the member role.
     * Only administrator could be invit/create new member.
     * 
     * @Route("/{id}/create", name="member_create")
     * @Method("post")
     * @Template("NeblionScrumBundle:Member:new.html.twig")
     * @param integer $id Project id
     */
    public function createAction($id)
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
        if (!$member or  !$member->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(new InvitationType());
        $request = $this->getRequest();
        $form->bindRequest($request);

        if ($form->isValid()) {
            // Load fos user manager
            $userManager = $this->container->get('fos_user.user_manager');
            
            // Retrieve the submited email 
            $email = $request->request->get($form->getName() . '[email]', null, true);
            
            // Load account associated to the email address
            $account = $userManager->findUserByUsernameOrEmail($email);
            
            // Load necessary entites
            // Load default Role (member);
            $role = $em->getRepository('NeblionScrumBundle:Role')->find(4);
            // Load default status (pending confirmation);
            $status = $em->getRepository('NeblionScrumBundle:MemberStatus')->find(1);
            
            // If there is an existing account create only a member entity
            // if not create a default account and a member entity
            $existAccount = $confirmationUrl = false;
            if (!$account) {
                $account = $userManager->createUser();
                $account->setEmail($email);
                $account->setUsername($email);
                
                // Generate a default password
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $password = '';
                for ($i = 0; $i <= 8; $i++) {
                    $password .= $chars[mt_rand(0, 61)];
                }
                $account->setPlainPassword($password);
                //$userManager->updatePassword($account);
                if (null === $account->getConfirmationToken()) {
                    $tokenGenerator = $this->container->get('fos_user.util.token_generator');
                    $account->setConfirmationToken($tokenGenerator->generateToken());
                }
                $userManager->updateUser($account, false);
                $confirmationUrl = $this->get('router')->generate('fos_user_registration_confirm', array('token' => $account->getConfirmationToken()), true);;
                
                // Create new Member
                $member = new Member();
                $member->setProject($project);
                $member->setAccount($account);
                $member->setRole($role);
                $member->setAdmin(false);
                $member->setStatus($status);
                $member->setSender($user);
                $em->persist($member);
            } else {
                $existAccount = true;
                // Create new Member
                $member = new Member();
                $member->setProject($project);
                $member->setAccount($account);
                $member->setSender($user);
                $member->setRole($role);
                $member->setAdmin(false);
                $member->setStatus($status);
                $em->persist($member);
            }
            
            $em->flush();
            
            // Send an email notification to the new member
            if (!$existAccount) {
                $this->get('neblion_mailer')->sendInvitationEmailMessage($account, $user, $project);
            } else {
                $this->get('neblion_mailer')->sendInvitationEmailMessage($account, $user, $project);
            }
            
            // Set flash message
            $this->get('session')->setFlash('success', 'Your invitation was sent successfully!');
            return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
        }

        return array(
            'project'           => $project,
            'form'              => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Member entity.
     * 
     * Only administrator could be invit/create new member.
     *
     * @Route("/{id}/edit", name="member_edit")
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

        $member = $em->getRepository('NeblionScrumBundle:Member')->load($id);
        if (!$member) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }
        
        $project = $member->getProject();

        // Check if current user is authorize to edit this member
        // Check if user is really a member of this project
        $currentMember = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$currentMember or !$currentMember->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        // Check if the member is enabled
        if ($member->getStatus()->getId() != 2) {
            // Set flash message
            $this->get('session')->setFlash('success', 'You could not edit this member, he was not enabled!');
            return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
        }
        
        $editForm = $this->createForm(new MemberType(), $member);

        return array(
            'project'   => $project,
            'member'    => $member,
            'form'      => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Member entity.
     * 
     * Only administrator could be invit/create new member.
     *
     * @Route("/{id}/update", name="member_update")
     * @Method("post")
     * @Template("NeblionScrumBundle:Member:edit.html.twig")
     */
    public function updateAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $member = $em->getRepository('NeblionScrumBundle:Member')->load($id);
        if (!$member) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }
        
        $project = $member->getProject();

        // Check if current user is authorize to edit this member
        // Check if user is really a member of this project
        $currentMember = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$currentMember or !$currentMember->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        $editForm   = $this->createForm(new MemberType(), $member);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($member);
            $em->flush();

            // Set flash message
            $this->get('session')->setFlash('success', 'Member was successfully updated !');
            return $this->redirect($this->generateUrl('member_edit', array('id' => $member->getId())));
        }

        return array(
            'member'      => $member,
            'edit_form'   => $editForm->createView(),
        );
    }
    
    /**
     * Member invitation
     *
     * @Route("/invitation", name="member_invitation")
     * @Template()
     */
    public function invitationAction()
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        // Load invitations
        $invitations = $em->getRepository('NeblionScrumBundle:Member')
                ->loadInvitations($user);
        
        return array(
            'invitations'   => $invitations,
        );
    }
    
    /**
     * Accept invitation
     *
     * @Route("/{id}/accept", name="member_invitation_accept")
     * @Template()
     */
    public function acceptAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $member = $em->getRepository('NeblionScrumBundle:Member')->load($id);
        if (!$member) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }
        
        $project = $member->getProject();

        // Check if member and user are the same account
        if ($member->getAccount()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }
        
        // Load Enabled status
        $status = $em->getRepository('NeblionScrumBundle:MemberStatus')->find(2);
        $member->setStatus($status);
        
        $em->flush();
        
        // Set flash message
        $this->get('session')->setFlash('success', 'You have successfully accepted invitation to this team !');
        return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
    }
    
    /**
     * Refuse invitation
     *
     * @Route("/{id}/refuse", name="member_invitation_refuse")
     * @Template()
     */
    public function refuseAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $member = $em->getRepository('NeblionScrumBundle:Member')->load($id);
        if (!$member) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }
        
        $project = $member->getProject();

        // Check if member and user are the same account
        if ($member->getAccount()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }
        
        // Load Invitation refused status
        $status = $em->getRepository('NeblionScrumBundle:MemberStatus')->find(4);
        $member->setStatus($status);
        
        $em->flush();
        
        // Set flash message
        $this->get('session')->setFlash('success', 'You have successfully refused invitation to this team !');
        return $this->redirect($this->generateUrl('neblion_scrum_welcome'));
    }
    
    /**
     * Re invitation
     *
     * @Route("/{id}/renew", name="member_invitation_renew")
     * @Template()
     */
    public function renewAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $member = $em->getRepository('NeblionScrumBundle:Member')->load($id);
        if (!$member) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }
        
        $project = $member->getProject();

        // Check if current user is authorize to edit this member
        // Check if user is really a member of this project
        $currentMember = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$currentMember or !$currentMember->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        // Load Invitation refused status
        $status = $em->getRepository('NeblionScrumBundle:MemberStatus')->find(1);
        $member->setStatus($status);
        
        $em->flush();
        
        // Send an email notification
        // FIXME
        $this->get('neblion_mailer')->sendInvitationEmailMessage($member->getAccount(), $user, $project);
        
        // Set flash message
        $this->get('session')->setFlash('success', 'You have successfully renew invitation to this member !');
        return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
    }
    
    /**
     * Remove an existing Member from a project.
     * 
     * Only admin can remove a member.
     * An admin can not remove himself if he is the last admin
     *
     * @Route("/{id}/remove", name="member_remove")
     * @Template()
     */
    public function removeAction($id)
    {
        // Check if user is authorized
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $member = $em->getRepository('NeblionScrumBundle:Member')->load($id);
        if (!$member) {
            throw $this->createNotFoundException('Unable to find Member entity.');
        }
        
        $project = $member->getProject();
        
        // Check if current user try to remove himself !
        if ($member->getAccount()->getId() == $user->getId()) {
            // Set flash message
            $this->get('session')->setFlash('error', 'You could not remove yourself from the team !');
            return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
        }

        // Check if current user is authorize to edit this member
        // Check if user is really a member of this project
        $currentMember = $em->getRepository('NeblionScrumBundle:Member')
                ->isMemberOfProject($user->getId(), $project->getId());
        if (!$currentMember or !$currentMember->getAdmin()) {
            throw new AccessDeniedException();
        }
        
        // Check if member has task in progress (in a sprint in progress) 
        // associated to him
        $tasks = $em->getRepository('NeblionScrumBundle:Task')
                ->getToDoInProgressForMember($member->getId());
        if (!empty($tasks)) {
            // Set flash message
            $this->get('session')->setFlash('error', 'Member could not be remove from team, there are tasks associated to him !');
            return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
        }
        
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->remove($member);
                $em->flush();
                
                // Send an email notification
                $this->get('neblion_mailer')->sendMemberRemoveNotification($member, $user, $project);
            
                // Set flash message
                $this->get('session')->setFlash('success', 'Member was removed from team with success!');
                return $this->redirect($this->generateUrl('member_list', array('id' => $project->getId())));
            }
        }

        return array(
            'project'   => $project,
            'member'    => $member,
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
