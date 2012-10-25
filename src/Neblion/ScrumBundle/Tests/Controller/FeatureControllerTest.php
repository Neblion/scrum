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
        //$this->assertEquals(4, $crawler->filter('span.required')->count());
        
        // Test form: empty fields
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array());
        
        // Test project name empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projecttype_name ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        // Test project description empty
        $this->assertTrue($crawler->filter('textarea#neblion_scrumbundle_projecttype_description ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        // Test project sprint_duration empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projecttype_sprint_duration ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        
               
        // Test form: name field length > 50 and sprint_duration = 0
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projecttype[name]'             => '012345678901234567890123456789012345678901234567890123456789',
            'neblion_scrumbundle_projecttype[description]'      => 'description',
            'neblion_scrumbundle_projecttype[sprint_duration]'  => 0,
        ));
        
        // name.length > 50
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projecttype_name ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value is too long. It should have 50 character or less.|This value is too long. It should have 50 characters or less.') {
                return false;
            }
        })->count() == 1);
        // Test project sprint_duration < 0
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projecttype_sprint_duration ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should be 1 or more.') {
                return false;
            }
        })->count() == 1);
        
    }
    
    public function testCreate()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/project/new');
        // Test html template
        $this->assertTrue($crawler->filter('html:contains("Project creation")')->count() == 1);
        //$this->assertEquals(4, $crawler->filter('span.required')->count());
        
        // Test form: empty fields
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projecttype[name]'             => 'project-creation',
            'neblion_scrumbundle_projecttype[description]'      => 'description',
            'neblion_scrumbundle_projecttype[sprint_duration]'  => 14,
        ));
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("project-creation Dashboard")')->count() == 1);
        
    }
    
    public function testEdit()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/project/1/edit');
        // Test html template
        $this->assertTrue($crawler->filter('html:contains("Project edition")')->count() == 1);
    }
    
    public function testUpdate()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/project/2/edit');
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projecttype[name]'             => 'project-creation-1',
            'neblion_scrumbundle_projecttype[description]'      => 'description-1',
            'neblion_scrumbundle_projecttype[sprint_duration]'  => 13,
        ));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Project was successfully updated!")')->count() == 1);
    }
    
    public function testDelete()
    {
        
    }
}
