<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Neblion\ScrumBundle\Tests\WebTestCase;

class ProjectControllerTest extends WebTestCase
{
    
    public function testProjects()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/');
        //var_dump($client->getResponse()->getContent());
        $this->assertTrue($crawler->filter('html:contains("Your projects")')->count() == 1);
        // Test number of project => 1 project but 2 li
        $this->assertTrue($crawler->filter('ul.project-list li')->count() == 2);
        // Test name of first project => Test
        $this->assertTrue($crawler->filter('ul.project-list li')->eq(1)->text() == 'Test');
    }
    
    public function testDashboard()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/project/1/dashboard');
        //var_dump($client->getResponse()->getContent());
        // Test 
        $this->assertTrue($crawler->filter('html:contains("Test Dashboard")')->count() == 1);
    }
    
    public function testBacklog()
    {
        
    }
    
    public function testNew()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/project/new');
        // Test html template
        $this->assertTrue($crawler->filter('html:contains("Project creation")')->count() == 1);
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $client->submit($form, array(
        ));
    }
    
    public function testCreate()
    {
        
    }
    
    public function testEdit()
    {
        
    }
    
    public function testUpdate()
    {
        
    }
    
    public function testDelete()
    {
        
    }
}
