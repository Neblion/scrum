<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Hour;


class LoadHourData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        // Load task
        $tasks = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Task')->findAll() as $item) {
            $tasks[$item->getId()] = $item;
        }
        
        $entities = array(
            // Hour Project 1 Sprint 1
            // Init
            array('task' => $tasks[1], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[2], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[3], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[4], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[5], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[6], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[7], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            array('task' => $tasks[8], 'hour' => 6, 'date' => new \DateTime('2012-09-05')),
            // Update
            array('task' => $tasks[1], 'hour' => 0, 'date' => new \DateTime('2012-09-06')),
            array('task' => $tasks[2], 'hour' => 0, 'date' => new \DateTime('2012-09-07')),
            array('task' => $tasks[3], 'hour' => 0, 'date' => new \DateTime('2012-09-10')),
            array('task' => $tasks[4], 'hour' => 0, 'date' => new \DateTime('2012-09-11')),
            array('task' => $tasks[5], 'hour' => 0, 'date' => new \DateTime('2012-09-12')),
            array('task' => $tasks[6], 'hour' => 0, 'date' => new \DateTime('2012-09-13')),
            array('task' => $tasks[7], 'hour' => 0, 'date' => new \DateTime('2012-09-14')),
            array('task' => $tasks[8], 'hour' => 0, 'date' => new \DateTime('2012-09-17')),
            
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
        return 9; 
    }
    
    private function newEntity($params)
    {
        // Create hour
        $entity = new Hour();
        $entity->setTask($params['task']);
        $entity->setHour($params['hour']);
        $entity->setDate($params['date']);
        $this->manager->persist($entity);
    }
    
    
}