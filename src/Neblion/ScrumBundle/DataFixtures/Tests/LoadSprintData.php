<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Sprint;
use Neblion\ScrumBundle\Entity\ProcessStatus;

class LoadSprintData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        // Load status
        $status = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:ProcessStatus')->findAll() as $item) {
            $status[$item->getId()] = $item;
        }
        
        $entities = array(
            array('name' => 'SP1', 'release' => 1, 'status' => $status[3], 'start' => new \DateTime('2012-09-05'), 'end' => new \DateTime('2012-09-18'), 'velocity' => 11), 
            array('name' => 'SP2', 'release' => 1, 'status' => $status[3], 'start' => new \DateTime('2012-09-19'), 'end' => new \DateTime('2012-10-02'), 'velocity' => 23), 
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
        return 6; 
    }
    
    private function newEntity($params)
    {
        // Create Sprint
        // Load release
        $projectRelease = $this->manager->getRepository('NeblionScrumBundle:ProjectRelease')->find($params['release']);
        $entity = new Sprint();
        $entity->setName($params['name']);
        $entity->setDescription($params['name'] . ' description');
        $entity->setStart($params['start']);
        $entity->setEnd($params['end']);
        $entity->setVelocity($params['velocity']);
        $entity->setStatus($params['status']);
        $entity->setProjectRelease($projectRelease);
        $this->manager->persist($entity);
    }
    
    
}