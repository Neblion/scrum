<?php

namespace Neblion\ScrumBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleRepositoryFunctionalTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testCountRole()
    {
        $roles = $this->em
            ->getRepository('NeblionScrumBundle:Role')
            ->findAll()
        ;

        $this->assertCount(4, $roles);
    }
}