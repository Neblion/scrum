<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Neblion\ScrumBundle\Entity\MemberStatus;

class LoadMemberStatusData implements FixtureInterface
{
    private $manager; 
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $entities = array(
            array('name' => 'Pending confirmation'),
            array('name' => 'Enabled'),
            array('name' => 'Disabled'),
            array('name' => 'Refused'),
        );
     
        foreach ($entities as $entity) {
            $this->newEntity($entity);
        }
        
        $this->manager->flush();
    }
    
    public function getOrder()
    {
        return 1; 
    }
    
    private function newEntity($params)
    {
        $memberStatus = new MemberStatus();
        $memberStatus->setName($params['name']);
        $this->manager->persist($memberStatus);
    }
    
    
}