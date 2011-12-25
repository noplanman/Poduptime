<?php
require_once('RemoteConnect.php');
class RemoteConnectTest extends PHPUnit_Framework_TestCase
{
  public function setUp(){ }
  public function tearDown(){ }
  public function testConnectionIsValid()
  {
    // test to ensure that the object from an fsockopen is valid
    $connObj = new RemoteConnect();
    $serverName = 'podupti.me';
    $this->assertTrue($connObj->connectToServer($serverName) !== false);
  }
}
?>
