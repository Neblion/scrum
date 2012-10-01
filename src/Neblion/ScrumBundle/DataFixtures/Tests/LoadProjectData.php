<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Neblion\ScrumBundle\Entity\Project;
use Neblion\ScrumBundle\Entity\ProcessStatus;

class LoadProjectData implements FixtureInterface
{
    private $manager; 
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $entities = array(
            array('name' => 'Test', 'description' => 'This is a test.'),
        );
     
        foreach ($entities as $entity) {
            $this->newEntity($entity);
        }
        
        $this->manager->flush();
    }
    
    public function getOrder()
    {
        return 4; 
    }
    
    private function newEntity($params)
    {
        $entity = new Project();
        $entity->setName($params['name']);
        $entity->setDescription($params['description']);
        $this->manager->persist($entity);
    }
    
    
}