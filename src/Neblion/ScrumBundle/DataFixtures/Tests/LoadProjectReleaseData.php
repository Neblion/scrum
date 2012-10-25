<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\ProjectRelease;

class LoadProjectReleaseData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        // Load project
        $project = $this->manager->getRepository('NeblionScrumBundle:Project')->find(1);
        // Load status
        $status = $this->manager->getRepository('NeblionScrumBundle:ProcessStatus')->find(1);
        
        $entities = array(
            array(
                'name'              => 'Release-1', 
                'description'       => 'Release-1 description',
                'start'             => new \DateTime('2012-10-03'),
                'end'               => new \DateTime('2012-10-16'),
                'project'           => $project,
                'status'            => $status,
            ),
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
        $release = new ProjectRelease();
        $release->setProject($params['project']);
        $release->setName($params['name']);
        $release->setDescription($params['description']);
        $release->setStart($params['start']);
        $release->setEnd($params['end']);
        $release->setStatus($params['status']);
        $this->manager->persist($release);
    }
    
    
}