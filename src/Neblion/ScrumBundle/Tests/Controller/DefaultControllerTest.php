<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Neblion\ScrumBundle\Tests\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        
        $this->assertTrue($crawler->filter('html:contains("Hello, world!")')->count() == 1);
        
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/');
        //var_dump($client->getResponse()->getContent());
        $this->assertTrue($crawler->filter('html:contains("Your projects")')->count() == 1);
        // Test number of project => 1 project but 2 li
        $this->assertTrue($crawler->filter('ul.project-list li')->count() == 2);
        // Test name of first project => Test
        $this->assertTrue($crawler->filter('ul.project-list li')->eq(1)->text() == 'Test');
    }
}
