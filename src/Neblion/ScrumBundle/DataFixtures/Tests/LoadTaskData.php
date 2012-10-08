<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Task;
use Neblion\ScrumBundle\Entity\ProcessStatus;


class LoadTaskData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        // Load story
        $stories = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Story')->findAll() as $item) {
            $stories[$item->getId()] = $item;
        }
        
        // Load members
        $members = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Member')->findAll() as $item) {
            $members[$item->getId()] = $item;
        }
        
        // Load status
        $status = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:ProcessStatus')->findAll() as $item) {
            $status[$item->getId()] = $item;
        }
        
        $entities = array(
            // Task sprint 1
            array('name' => 'Task 1A1', 'story' => $stories[1], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1A2', 'story' => $stories[1], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1B1', 'story' => $stories[2], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1B2', 'story' => $stories[2], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1C1', 'story' => $stories[3], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1C2', 'story' => $stories[3], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1D1', 'story' => $stories[4], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1D2', 'story' => $stories[4], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            // Task sprint 2
            array('name' => 'Task 1E1', 'story' => $stories[5], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1E2', 'story' => $stories[5], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1F1', 'story' => $stories[6], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1F2', 'story' => $stories[6], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1G1', 'story' => $stories[7], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1G2', 'story' => $stories[7], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1H1', 'story' => $stories[8], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
            array('name' => 'Task 1H2', 'story' => $stories[8], 'member' => $members[4], 'status' => $status[3], 'hour' => 6),
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
        return 8; 
    }
    
    private function newEntity($params)
    {
        // Create task
        $entity = new Task();
        $entity->setStory($params['story']);
        $entity->setMember($params['member']);
        $entity->setStatus($params['status']);
        $entity->setName($params['name']);
        $entity->setDescription($params['name'] . ' description');
        $entity->setHour($params['hour']);
        $this->manager->persist($entity);
    }
    
    
}