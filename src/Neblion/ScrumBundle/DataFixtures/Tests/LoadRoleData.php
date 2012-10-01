<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Neblion\ScrumBundle\Entity\Role;

class LoadRoleData implements FixtureInterface
{
    private $manager; 
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $entities = array(
            array('name' => 'Product owner'),
            array('name' => 'Scrumaster'),
            array('name' => 'Developer'),
            array('name' => 'Member'),
        );
     
        foreach ($entities as $entity) {
            $this->newEntity($entity);
        }
        
        $this->manager->flush();
    }
    
    public function getOrder()
    {
        return 3; 
    }
    
    private function newEntity($params)
    {
        $entity = new Role();
        $entity->setName($params['name']);
        $this->manager->persist($entity);
    }
    
    
}