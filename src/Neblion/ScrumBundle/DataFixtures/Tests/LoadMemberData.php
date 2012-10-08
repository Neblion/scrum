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
        
        $entities = array(
            array('name' => 'admin', 'team' => 1, 'role' => 4, 'admin' => true),
            array('name' => 'productowner', 'team' => 1, 'role' => 1, 'admin' => false),
            array('name' => 'scrumaster', 'team' => 1, 'role' => 2, 'admin' => false),
            array('name' => 'developer', 'team' => 1, 'role' => 3, 'admin' => false),
            array('name' => 'member', 'team' => 1, 'role' => 4, 'admin' => false)
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
        $profile->setLocation($params['name']);
        $this->manager->persist($profile);
        
        // Create member
        // Load team
        $team = $this->manager->getRepository('NeblionScrumBundle:Team')->find($params['team']);
        // Load role
        $role = $this->manager->getRepository('NeblionScrumBundle:Role')->find($params['role']);
        // Load status
        $status = $this->manager->getRepository('NeblionScrumBundle:MemberStatus')->find(2);
        $member = new Member();
        $member->setAccount($user);
        $member->setTeam($team);
        $member->setRole($role);
        $member->setStatus($status);
        $member->setAdmin($params['admin']);
        $this->manager->persist($member);
    }
    
    
}