<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Project;
use Neblion\ScrumBundle\Entity\Feature;

class LoadFeatureData extends AbstractFixture implements OrderedFixtureInterface
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
        
        $entities = array(
            array(
                'name'              => 'Feature-1', 
                'description'       => 'Feature-1 description',
                'color'             => '#111111',
                'project'           => $project,
            ),
            array(
                'name'              => 'Feature-2', 
                'description'       => 'Feature-2 description',
                'color'             => '#222222',
                'project'           => $project,
            ),
            array(
                'name'              => 'Feature-3', 
                'description'       => 'Feature-3 description',
                'color'             => '#333333',
                'project'           => $project,
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
        return 4; 
    }
    
    private function newEntity($params)
    {
        $feature = new Feature();
        $feature->setProject($params['project']);
        $feature->setName($params['name']);
        $feature->setDescription($params['description']);
        $feature->setColor($params['color']);
        $this->manager->persist($feature);
    }
    
    
}