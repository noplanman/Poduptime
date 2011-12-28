<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
 
class WebTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp()
    {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl('http://podupti.me/');
    }
 
    public function testTitle()
    {
        $this->open('http://podupti.me/');
        $this->assertTitle('DIaspora Pod uptime - Find your new social home');
    }
}
?>

