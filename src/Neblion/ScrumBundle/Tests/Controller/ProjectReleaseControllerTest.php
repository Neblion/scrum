<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Neblion\ScrumBundle\Tests\WebTestCase;

class ProjectReleaseControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/release/1');
        // Test html template
        // Title of main widget-box
        $this->assertTrue($crawler->filter('html:contains("Releases list")')->count() == 1);
        // 2 tr => 2 releases for project->id = 1
        $this->assertEquals(2, $crawler->filter('table tbody tr')->count());
    }
    
    
    public function testNew()
    {
        // Test that simple member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/release/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that developer member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/release/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that scrumaster member could add a feature
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/release/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could add a feature
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/release/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that admin member could add a feature
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/release/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test form: empty fields
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array());
        
        // Test project name empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projectreleasetype_name ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        // Test project description empty
        $this->assertTrue($crawler->filter('textarea#neblion_scrumbundle_projectreleasetype_description ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        // Test project sprint_duration empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projectreleasetype_start ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        
        // Test form: name field length > 50 and color not hexa code
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projectreleasetype[name]'             => '012345678901234567890123456789012345678901234567890123456789',
            'neblion_scrumbundle_projectreleasetype[description]'      => 'description',
            'neblion_scrumbundle_projectreleasetype[start]'            => 'start',
            'neblion_scrumbundle_projectreleasetype[end]'              => 'end',
        ));
        // name.length > 50
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projectreleasetype_name ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value is too long. It should have 50 character or less.|This value is too long. It should have 50 characters or less.') {
                return false;
            }
        })->count() == 1);
        // start is not a date value
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projectreleasetype_start ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value is not valid.') {
                return false;
            }
        })->count() == 1);
        // end is not a date value
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_projectreleasetype_end ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value is not valid.') {
                return false;
            }
        })->count() == 1);
        
        // Test form with start date in an another release
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projectreleasetype[name]'             => 'release-test',
            'neblion_scrumbundle_projectreleasetype[description]'      => 'description',
            'neblion_scrumbundle_projectreleasetype[start]'            => '04/10/2012',
        ));
        $this->assertTrue($crawler->filter('html:contains("The release\'s dates overlap (Release-1)")')->count() == 1);
    }
    
    public function testCreate()
    {
        // Test that productowner member could not add a feature
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/release/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test form
        // Test form with start date in an another release
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projectreleasetype[name]'             => 'release-test',
            'neblion_scrumbundle_projectreleasetype[description]'      => 'description',
            'neblion_scrumbundle_projectreleasetype[start]'            => '17/10/2012',
        ));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Release was created with success!")')->count() == 1);
        $this->assertEquals(3, $crawler->filter('table tbody tr')->count());
    }
    
    public function testEdit()
    {
        // Test that simple member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/release/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that developer member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/release/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that scrumaster member could edit a feature
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/release/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could edit a feature
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/release/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could edit a feature
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/release/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdate()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/release/4/edit');
        
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_projectreleasetype[name]'             => 'release-test-update',
            'neblion_scrumbundle_projectreleasetype[description]'      => 'description-update',
            'neblion_scrumbundle_projectreleasetype[start]'            => '17/10/2012',
        ));
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Release was updated with success!")')->count() == 1);
        
    }
    
    public function testDelete()
    {
        $client = $this->login('admin', 'test');
        
        // Test normal deletion
        $crawler = $client->request('GET', '/release/4/delete');
        
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array());
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        // Test alert msg that confirm deletion
        $this->assertTrue($crawler->filter('html:contains("Release was deleted with success!")')->count() == 1);
        // 4 tr => 4 features for project->id = 1
        $this->assertEquals(2, $crawler->filter('table tbody tr')->count());
        
        // Test release deletion that could not be possible because of associated stories
        $crawler = $client->request('GET', '/release/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        // Test alert msg that confirm deletion
        $this->assertTrue($crawler->filter('html:contains("Impossible to delete this release, there is at least a sprint attached to it!")')->count() == 1);
    }
}
