<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
 
class WebTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp()
    {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl('http://podupti.me/');
        $this->setHost('localhost');
        $this->setPort(4444);
    }
 
    public function testTitle()
    {
        $this->assertTitle('Diaspora Pod uptime - Find your new social home');
    }
}
?>

