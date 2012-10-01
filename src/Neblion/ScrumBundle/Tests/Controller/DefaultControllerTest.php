<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($crawler->filter('html:contains("Dashboard")')->count() == 0);
        $this->assertTrue($crawler->filter('html:contains("Hello, world!")')->count() == 1);
    }
}
