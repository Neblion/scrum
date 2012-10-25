<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Neblion\ScrumBundle\Tests\WebTestCase;

class FeatureControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/feature/1/list');
        // Test html template
        // Title of main widget-box
        $this->assertTrue($crawler->filter('html:contains("Features list")')->count() == 1);
        // 4 tr => 4 features for project->id = 1
        $this->assertEquals(4, $crawler->filter('table tbody tr')->count());
    }
    
    
    public function testNew()
    {
        // Test that simple member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/feature/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that developer member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/feature/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that scrumaster member could add a feature
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/feature/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could add a feature
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/feature/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that admin member could add a feature
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/feature/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test form: empty fields
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array());
        
        // Test project name empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_featuretype_name ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        // Test project description empty
        $this->assertTrue($crawler->filter('textarea#neblion_scrumbundle_featuretype_description ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        // Test project sprint_duration empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_featuretype_color ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
        
        
        // Test form: name field length > 50 and color not hexa code
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_featuretype[name]'             => '012345678901234567890123456789012345678901234567890123456789',
            'neblion_scrumbundle_featuretype[description]'      => 'description',
            'neblion_scrumbundle_featuretype[color]'            => 'color',
        ));
        
        // name.length > 50
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_featuretype_name ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value is too long. It should have 50 character or less.|This value is too long. It should have 50 characters or less.') {
                return false;
            }
        })->count() == 1);
        // color not hexa code
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_featuretype_color ~ span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This is not a valid hexadecimal color!') {
                return false;
            }
        })->count() == 1);
    }
    
    public function testCreate()
    {
        // Test that productowner member could not add a feature
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/feature/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_featuretype[name]'             => 'feature-create-test',
            'neblion_scrumbundle_featuretype[description]'      => 'description',
            'neblion_scrumbundle_featuretype[color]'            => '#111111',
        ));
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Feature was created with success!")')->count() == 1);
    }
    
    public function testEdit()
    {
        // Test that simple member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/feature/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that developer member could not add a feature
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/feature/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that scrumaster member could edit a feature
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/feature/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could edit a feature
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/feature/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could edit a feature
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/feature/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
    }
    
    public function testUpdate()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/feature/5/edit');
        
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_featuretype[name]'             => 'feature-update-test',
            'neblion_scrumbundle_featuretype[description]'      => 'description-update',
            'neblion_scrumbundle_featuretype[color]'            => '#222222',
        ));
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Feature was updated with success!")')->count() == 1);
        
    }
    
    public function testDelete()
    {
        $client = $this->login('admin', 'test');
        
        // Test normal deletion
        $crawler = $client->request('GET', '/feature/5/delete');
        
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array());
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        // Test alert msg that confirm deletion
        $this->assertTrue($crawler->filter('html:contains("Feature was deleted with success!")')->count() == 1);
        // 4 tr => 4 features for project->id = 1
        $this->assertEquals(4, $crawler->filter('table tbody tr')->count());
        
        // Test feature deletion that could not be possible because of associated stories
        $crawler = $client->request('GET', '/feature/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        // Test alert msg that confirm deletion
        $this->assertTrue($crawler->filter('html:contains("You could not delete this Feature, stories associated with it!")')->count() == 1);
    }
}
