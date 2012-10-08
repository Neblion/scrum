<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\Role;

class LoadRoleData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
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
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; 
    }
    
    private function newEntity($params)
    {
        $entity = new Role();
        $entity->setName($params['name']);
        $this->manager->persist($entity);
    }
    
    
}