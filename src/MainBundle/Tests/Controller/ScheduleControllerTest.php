<?php
namespace AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{

    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testindexAction()
    {

        $crawler = $this->client->request('POST', '/schedule/new');

        $this->client->getContainer()->get('session');
       
        $form = $crawler->selectButton('Create and Send Invitation')->form();
       
        $crawler = $this->client->submit($form,
                    array(  'schedule[title]'           => 'TestCasesTitle',
                            'schedule[scheduledatetime]'=> '2015-05-29 10:04',
                            'schedule[phone]'           => '3333333333',
                            'schedule[firstname]'       => 'TestCasesFirst',
                            'schedule[lastname]'        => 'TestCasesLast',
                            'schedule[email]'           => 'testcasesfirst@gmail.com',
                            'schedule[owaonekeycode]'   => 'D78141BA-C167-22D0-07A8-075E4B75BA16',
                            'schedule[owauuid]'         => 'D78141BA-C167-22D0-07A8-075E4B75BA16')
                    );
        $this->assertGreaterThan(-1, $crawler->filter('html:contains("/schedule/schedule_confirm/")')->count());
    }

    /*
    * @depends testIndex
    **/
    public function testCancelAction()
    {
        $this->client->getContainer()->get('session');
        $firewall = 'secured_area';
        $crawler = $this->client->request('GET', '/schedule/new');
        $link    = $crawler->filter('a:contains("Cancel")')->eq(0)->link();
        $crawler = $this->client->click($link);
        
        //Check the h2 has the blog title in it
        $this->assertEquals(1, $crawler->filter('html:contains("Remote Patient")')->count());
    }
}