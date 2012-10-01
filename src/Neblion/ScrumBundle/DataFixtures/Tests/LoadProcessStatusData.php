<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Neblion\ScrumBundle\Entity\ProcessStatus;

class LoadProcessStatusData implements FixtureInterface
{
    private $manager; 
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $entities = array(
            array('name' => 'To Do'),
            array('name' => 'In Progress'),
            array('name' => 'Done'),
        );
     
        foreach ($entities as $entity) {
            $this->newEntity($entity);
        }
        
        $this->manager->flush();
    }
    
    public function getOrder()
    {
        return 2; 
    }
    
    private function newEntity($params)
    {
        $status = new ProcessStatus();
        $status->setName($params['name']);
        $this->manager->persist($status);
    }
    
    
}