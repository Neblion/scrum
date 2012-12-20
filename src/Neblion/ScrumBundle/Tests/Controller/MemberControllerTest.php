<?php

namespace Neblion\ScrumBundle\Tests\Controller;

use Neblion\ScrumBundle\Tests\WebTestCase;

class MemberControllerTest extends WebTestCase
{
    public function testNew()
    {
        // Test that simple member could not add a member
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that developer member could not add a member
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that scrumaster member could not add a member
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could not add a member
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that admin member could add a member
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test form: empty fields
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array());
        
        // Test email empty
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_membertype_email ~ ul li span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'This value should not be blank.') {
                return false;
            }
        })->count() == 1);
 
        // Test form: email invalid
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_membertype[email]' => 'email',
        ));
        // email invalid
        $this->assertTrue($crawler->filter('input#neblion_scrumbundle_membertype_email ~ ul li span.help-inline')->reduce(function ($node, $i) {
            if ($node->nodeValue != 'Invalid email address') {
                return false;
            }
        })->count() == 1);
    }

    public function testCreate()
    {
        // Test that scrumaster member could not add a member
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // Test creation
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/member/1/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // Test form
        // Test form with start date in an another release
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, array(
            'neblion_scrumbundle_membertype[email]' => 'new-member@test.com',
        ));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Your invitation was sent successfully!")')->count() == 1);
        $this->assertEquals(3, $crawler->filter('table#not-validated-members tbody tr')->count());
    }

    public function testEdit()
    {
        // Test that simple member could not edit a member
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/member/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that developer member could not edit a member
        $client = $this->login('member', 'test');
        $crawler = $client->request('GET', '/member/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that scrumaster member could not edit a member
        $client = $this->login('scrumaster', 'test');
        $crawler = $client->request('GET', '/member/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test that productowner member could not edit a member
        $client = $this->login('productowner', 'test');
        $crawler = $client->request('GET', '/member/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
        // Test edition of a member that was not enabled
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/member/6/edit');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("You could not edit this member, he was not enabled!")')->count() == 1);
        
        // Test that admin member could edit a member
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/member/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdate()
    {
        
        // Test that admin member could edit a member
        $client = $this->login('admin', 'test');
        
        // Test classic update
        $crawler = $client->request('GET', '/member/3/edit');
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $form['neblion_scrumbundle_membertype[admin]']->tick();
        $form['neblion_scrumbundle_membertype[role]'] = 2;
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Member was successfully updated !")')->count() == 1);
        
        // Test remove admin privilege where not only one admin
        $crawler = $client->request('GET', '/member/3/edit');
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $form['neblion_scrumbundle_membertype[admin]']->untick();
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Member was successfully updated !")')->count() == 1);
        
        // Test remove admin privilege where only one admin
        $crawler = $client->request('GET', '/member/1/edit');
        // Test form
        $form = $crawler->selectButton('submit')->form();
        $form['neblion_scrumbundle_membertype[admin]']->untick();
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Member was the last admin fot the project, you can not remove his admin privilege !")')->count() == 1);
    }

    /*
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
     */
    
    public function testInvitation()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/member/invitation');
        $this->assertTrue($crawler->filter('html:contains("No invitation")')->count() == 1);
        
        $client = $this->login('invit-accepted', 'test');
        $crawler = $client->request('GET', '/member/invitation');
        $this->assertEquals(1, $crawler->filter('table tbody tr')->count());
    }
    
    public function testAccept()
    {
        $client = $this->login('invit-accepted', 'test');
        $crawler = $client->request('GET', '/member/6/accept');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("You have successfully accepted invitation to this team !")')->count() == 1);
        $this->assertEquals(6, $crawler->filter('table#validated-members tbody tr')->count());
    }
    
    public function testRefuse()
    {
        $client = $this->login('invit-refused', 'test');
        $crawler = $client->request('GET', '/member/7/refuse');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("You have successfully refused invitation to this team !")')->count() == 1);
    }
    
    public function testList()
    {
        $client = $this->login('admin', 'test');
        $crawler = $client->request('GET', '/member/1/list');
        
        // Not validated members = 2
        $this->assertEquals(2, $crawler->filter('table#not-validated-members tbody tr')->count());
        
        // Validated members = 6
        $this->assertEquals(6, $crawler->filter('table#validated-members tbody tr')->count());
    }
}
