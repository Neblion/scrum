<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Neblion\ScrumBundle\Entity\Account;
use Neblion\ScrumBundle\Entity\Profile;
use Neblion\ScrumBundle\Entity\Member;

class LoadMemberData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $manager; 
    
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        // Load team
        $team = $this->manager->getRepository('NeblionScrumBundle:Team')->find(1);
        // Load role
        $roles = $this->loadRoles();
        // Load status
        $status = $this->loadStatus();
                
        $entities = array(
            array(
                'name'      => 'admin', 
                'team'      => $team, 
                'role'      => $roles[4],
                'status'    => $status[2],
                'admin' => true),
            array(
                'name'      => 'productowner', 
                'team'      => $team, 
                'role'      => $roles[1], 
                'status'    => $status[2],
                'admin'     => false),
            array(
                'name'      => 'scrumaster', 
                'team'      => $team, 
                'role'      => $roles[2], 
                'status'    => $status[2],
                'admin'     => false),
            array(
                'name'      => 'developer', 
                'team'      => $team, 
                'role'      => $roles[3], 
                'status'    => $status[2],
                'admin'     => false),
            array(
                'name'      => 'member', 
                'team'      => $team, 
                'role'      => $roles[4], 
                'status'    => $status[2],
                'admin'     => false),
            
        );
        
        foreach ($entities as $entity) {
            $this->newEntity($entity);
        }
        
        $this->manager->flush();
        
        // Load sender for invitation
        $sender = $this->manager->getRepository('NeblionScrumBundle:Member')->find(1);
        // Load invit
        $entities = array(
            array(
                'name'      => 'invit-accepted', 
                'team'      => $team, 
                'role'      => $roles[4], 
                'status'    => $status[1],
                'sender'    => $sender,
                'admin'     => false),
            array(
                'name'      => 'invit-refused', 
                'team'      => $team, 
                'role'      => $roles[4], 
                'status'    => $status[1],
                'sender'    => $sender,
                'admin'     => false),
        );
                
        foreach ($entities as $entity) {
            $this->newEntity($entity);
        }
        
        $this->manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5; 
    }
    
    private function loadRoles()
    {
        $roles = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Role')->findAll() as $role) {
            $roles[$role->getId()] = $role;
        }
        return $roles;
    }
    
    private function loadStatus()
    {
        $results = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:MemberStatus')->findAll() as $status) {
            $results[$status->getId()] = $status;
        }
        return $results;
    }
    
    private function newEntity($params)
    {
        // Create account
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setUsername($params['name']);
        $user->setEmail($params['name'] . '@test.com');
        $user->setPlainPassword('test');
        $user->setEnabled(true);
        $userManager->updateUser($user, false);
        $this->manager->persist($user);
        
        // Create profile
        $profile = new Profile();
        $profile->setAccount($user);
        $profile->setFirstname($params['name']);
        $profile->setLastname($params['name']);
        //$profile->setLocation($params['name']);
        $this->manager->persist($profile);
        
        // Create member
        $member = new Member();
        $member->setAccount($user);
        $member->setTeam($params['team']);
        $member->setRole($params['role']);
        $member->setStatus($params['status']);
        if ($params['status']->getId() == 1) {
            $member->setSender($params['sender']->getAccount());
        }
        $member->setAdmin($params['admin']);
        $this->manager->persist($member);
    }
    
    
}