<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Story;
use Neblion\ScrumBundle\Entity\ProcessStatus;


class LoadStoryData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        // Load projects
        $projects = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Project')->findAll() as $item) {
            $projects[$item->getId()] = $item;
        }
        
        // Load sprints
        $sprints = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Sprint')->findAll() as $item) {
            $sprints[$item->getId()] = $item;
        }
        
        // Load features
        $features = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:Feature')->findAll() as $item) {
            $features[$item->getId()] = $item;
        }
        
        // Load status
        $status = array();
        foreach ($this->manager->getRepository('NeblionScrumBundle:ProcessStatus')->findAll() as $item) {
            $status[$item->getId()] = $item;
        }
        
        // Load story type
        $storyType = $this->manager->getRepository('NeblionScrumBundle:StoryType')->find(1);
        
        $entities = array(
            array('name' => 'Story 1A', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[1], 'status' => $status[3], 'estimate' => 1, 'position' => 1),
            array('name' => 'Story 1B', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[1], 'status' => $status[3], 'estimate' => 3, 'position' => 2),
            array('name' => 'Story 1C', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[1], 'status' => $status[3], 'estimate' => 5, 'position' => 3),
            array('name' => 'Story 1D', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[1], 'status' => $status[3], 'estimate' => 2, 'position' => 4),
            array('name' => 'Story 1E', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[2], 'status' => $status[3], 'estimate' => 8, 'position' => 1),
            array('name' => 'Story 1F', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[2], 'status' => $status[3], 'estimate' => 13, 'position' => 2),
            array('name' => 'Story 1G', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[2], 'status' => $status[3], 'estimate' => 2, 'position' => 3),
            array('name' => 'Story 1H', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => $sprints[2], 'status' => $status[2], 'estimate' => 8, 'position' => 4),
            array('name' => 'Story 1I', 'project' => $projects[1], 'type' => $storyType, 'feature' => $features[1], 'sprint' => false, 'status' => $status[1], 'estimate' => 5, 'position' => 1),
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
        return 7; 
    }
    
    private function newEntity($params)
    {
        // Create story
        $entity = new Story();
        $entity->setProject($params['project']);
        $entity->setFeature($params['feature']);
        if ($params['sprint']) {
            $entity->setSprint($params['sprint']);
        }
        $entity->setStatus($params['status']);
        $entity->setType($params['type']);
        $entity->setName($params['name']);
        $entity->setDescription($params['name'] . ' description');
        $entity->setPosition($params['position']);
        $entity->setEstimate($params['estimate']);
        $this->manager->persist($entity);
    }
    
    
}