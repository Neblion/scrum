<?php

namespace Neblion\ScrumBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberStatusRepositoryFunctionalTest extends WebTestCase
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
    
    public function testCountMemberStatus()
    {
        $items = $this->em
            ->getRepository('NeblionScrumBundle:MemberStatus')
            ->findAll()
        ;

        $this->assertCount(4, $items);
    }
}