<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Project;
use Neblion\ScrumBundle\Entity\ProcessStatus;
use Neblion\ScrumBundle\Entity\Team;
use Neblion\ScrumBundle\Entity\ProjectRelease;
use Neblion\ScrumBundle\Entity\Feature;

class LoadProjectData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $entities = array(
            array(
                'name'              => 'Test', 
                'description'       => 'This is a test.', 
                'sprint_start_day'  => 3,
                'sprint_duration'   => 14,
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
        // Create project
        $entity = new Project();
        $entity->setName($params['name']);
        $entity->setDescription($params['description']);
        $entity->setSprintStartDay($params['sprint_start_day']);
        $entity->setSprintDuration($params['sprint_duration']);
        $this->manager->persist($entity);
        
        // Create team
        $team = new Team();
        $team->setName($entity->getName());
        $team->setProject($entity);
        $this->manager->persist($team);
        
        // Create default release
        $releaseStatus = $this->manager->getRepository('NeblionScrumBundle:ProcessStatus')->find(3);
        $release = new ProjectRelease();
        $release->setProject($entity);
        $release->setName('Default');
        $release->setDescription('Default release');
        $release->setStart(new \DateTime('2012-09-01'));
        $release->setEnd(new \DateTime('2012-10-02'));
        $release->setStatus($releaseStatus);
        $this->manager->persist($release);
        
        // Create default feature
        $feature = new Feature();
        $feature->setProject($entity);
        $feature->setName('Default');
        $feature->setDescription('Default feature');
        $feature->setColor('#ffffff');
        $this->manager->persist($feature);
    }
    
    
}