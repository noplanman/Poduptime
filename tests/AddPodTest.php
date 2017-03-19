<?php declare(strict_types = 1);

namespace Diasporg\Poduptime\Tests;

class AddPodTest extends PoduptimeTestCase
{
  protected static $test_file = __DIR__ . '/../db/add.php';
  private static $nodeinfo = '{"version":"1.0","software":{"name":"diaspora","version":"0.6.0.0"},"protocols":{"inbound":["diaspora"],"outbound":["diaspora"]},"services":{"inbound":[],"outbound":["twitter","tumblr","facebook","wordpress"]},"openRegistrations":true,"usage":{"users":{"total":76543,"activeHalfyear":3456,"activeMonth":1234},"localPosts":567890,"localComments":456789},"metadata":{"nodeName":"diaspora*","xmppChat":true}}';

  /**
   * @runInSeparateProcess
   */
  public function testNoDomain()
  {
    $_GET['domain'] = '';

    $output = $this->execTestFile();

    self::assertEquals('no pod domain given', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testPodAlreadyRegistered()
  {
    $_GET['domain'] = 'test.pod';

    $output = $this->execTestFile();

    self::assertStringStartsWith('domain already exists and is registered to an owner', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testPodValidated()
  {
    $_GET['domain'] = 'noemail.test.pod';
    $_GET['digtxt'] = '123';

    $output = $this->execTestFile();

    self::assertStringStartsWith('domain validated, you can now add details', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testInvalidNodeInfo()
  {
    $_GET['domain']   = 'other.test.pod';
    $_GET['nodeinfo'] = '';

    $output = $this->execTestFile();

    self::assertStringStartsWith('Could not validate your pod, check your setup!', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testPodSuccessfullyAdded()
  {
    // Make sure we start fresh.
    \RedBeanPHP\R::exec('DELETE FROM pods WHERE domain = ?', ['new.test.pod']);

    $_GET['domain']   = 'new.test.pod';
    $_GET['nodeinfo'] = self::$nodeinfo;
    $_GET['digtxt']   = '123';

    $output = $this->execTestFile();

    self::assertStringStartsWith('Your pod has ssl and is valid', $output);
    self::assertContains('Data successfully inserted!', $output);

    $new_pod = \RedBeanPHP\R::findOne('pods', 'domain = ?', ['new.test.pod']);
    self::assertInstanceOf(\RedBeanPHP\OODBBean::class, $new_pod);
    self::assertEquals('new.test.pod', $new_pod['domain']);
  }

  /**
   * @runInSeparateProcess
   * @depends testPodSuccessfullyAdded
   */
  public function testPodCanBeValidated()
  {
    $_GET['domain']   = 'new.test.pod';
    $_GET['nodeinfo'] = self::$nodeinfo;
    $_GET['digtxt']   = '';

    $output = $this->execTestFile();

    self::assertStringStartsWith('domain already exists, you can claim the domain by adding a DNS TXT record', $output);
  }
}
