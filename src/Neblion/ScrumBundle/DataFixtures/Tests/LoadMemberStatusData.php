<?php
namespace Neblion\ScrumBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Neblion\ScrumBundle\Entity\MemberStatus;

class LoadMemberStatusData extends AbstractFixture implements OrderedFixtureInterface
{
    private $manager; 
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $entities = array(
            array('name' => 'Pending confirmation'),
            array('name' => 'Enabled'),
            array('name' => 'Disabled'),
            array('name' => 'Refused'),
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
        return 3; 
    }
    
    private function newEntity($params)
    {
        $memberStatus = new MemberStatus();
        $memberStatus->setName($params['name']);
        $this->manager->persist($memberStatus);
    }
    
    
}