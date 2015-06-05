<?php
namespace MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OptionControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }
	
	//test cases for the option link	
    public function testIndex()
    {
        $this->client->getContainer()->get('session');
        $crawler = $this->client->request('POST', '/option');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Option page")')->count() > 0);
	}

    /*
	* @depends testIndex
	**/
    public function testInviteRedirect()
    {
	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/option');
		
		$link 	 = $crawler->filter('a:contains("I would like")')->eq(0)->link();
		$crawler = $this->client->click($link);	

    	// Check the h2 has the blog title in it
    	$this->assertEquals(1, $crawler->filter('h2:contains("MedInquiry form")')->count());
	}

	/*
	* @depends testIndex
	**/
	public function testPresentationRedirect()
    {

	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/option');
     	
		$link 	 = $crawler->filter('a:contains("for a 1:1 remote presentation")')->eq(0)->link();
		$crawler = $this->client->click($link);
		
    	// Check the h2 has the blog title in it
    	$this->assertEquals(1, $crawler->filter('html:contains("index2")')->count());
	}

	/*
	* @depends testIndex
	**/
	public function testRemotePatientRedirect()
    {
	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/option');
     			
		$link 	 = $crawler->filter('a:contains("I need to set/manage/host patient education sessions")')->eq(0)->link();
		$crawler = $this->client->click($link);

    	// Check the h2 has the blog title in it
    	$this->assertEquals(1, $crawler->filter('h2:contains("Remote Patient")')->count());
	}

	/*
	* @depends testIndex
	**/
	public function testWebinarRedirect()
    {

	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/option');
		
		$link 	 = $crawler->filter('a:contains("For a Webinar Presentation")')->eq(0)->link();
		$crawler = $this->client->click($link);

    	// Check the h2 has the blog title in it
    	$this->assertEquals(1, $crawler->filter('h2:contains("index2")')->count());
	}

	/*
	* @depends testIndex
	**/
	public function testPolicyRedirect()
    {
	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/option');
		$link 	 = $crawler->filter('a:contains("Privacy Policy")')->eq(0)->link();
		$crawler = $this->client->click($link);
		
    	// Check the h2 has the blog title in it
    	$this->assertEquals(1, $crawler->filter('html:contains("150112_Legal%20Policy_UCBDandelion.pdf")')->count());
	}

	/*
	* @depends testIndex
	**/
    public function testremoteRedirect()
    {

	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/remote_patient_option');
		
		$link 	 = $crawler->filter('a:contains("I need to set a")')->eq(0)->link();
		$crawler = $this->client->click($link);
	
    	// Check the h2 has the blog title in it
    	$this->assertEquals(1,$crawler->filter('html:contains("Please indicate the title of your presentation")')->count());
	}

	/*
	* @depends testIndex
	**/
    public function testremoteGetRedirect()
    {

	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/remote_patient_option');
		     
		$link 	 = $crawler->filter('a:contains("a patient education sessions I have scheduled")')->eq(0)->link();
		$crawler = $this->client->click($link);
		
    	// Check the h2 has the blog title in it
    	$this->assertEquals(0,$crawler->filter('html:contains("Go To Remote Presentation Platform")')->count());
	}

	/*
	* @depends testIndex
	**/
    public function testremoteUpcomingRedirect()
    {
	    $this->client->getContainer()->get('session');
    	$firewall = 'secured_area';
		$crawler = $this->client->request('GET', '/remote_patient_option');
     			
		$link 	 = $crawler->filter('a:contains("I am hosting a 1:1 patient education session")')->eq(0)->link();
		$crawler = $this->client->click($link);

    	// Check the h2 has the blog title in it
    	$this->assertEquals(0,$crawler->filter('html:contains("Go To Remote Presentation Platform")')->count());
	}

}
