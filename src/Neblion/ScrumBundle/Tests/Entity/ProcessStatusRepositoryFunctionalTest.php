<?php

namespace Neblion\ScrumBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProcessStatusRepositoryFunctionalTest extends WebTestCase
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
    
    public function testCountProcessStatus()
    {
        $items = $this->em
            ->getRepository('NeblionScrumBundle:ProcessStatus')
            ->findAll()
        ;

        $this->assertCount(3, $items);
    }
}