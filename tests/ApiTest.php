<?php declare(strict_types = 1);

namespace Diasporg\Poduptime\Tests;

class ApiTest extends PoduptimeTestCase
{
  protected static $test_file = __DIR__ . '/../api.php';
  private static $api_key = '4r45tg';
 
  /**
   * @runInSeparateProcess
   */
  public function testNoApiKey()
  {
    $_GET['key'] = '';

    $output = $this->execTestFile();

    self::assertEquals('Invalid API key', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testApiBasicFormat()
  {
    $_GET['key'] = self::$api_key;

    $output = $this->execTestFile();

    self::assertContains('Located in', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testApiGeoRSSFormat()
  {
    $_GET['key']    = self::$api_key;
    $_GET['format'] = 'georss';

    $output = $this->execTestFile();

    self::assertStringStartsWith('<?xml version="1.0"', $output);
    self::assertContains('<title>Diaspora Pods</title>', $output);
    self::assertContains('<link href="https://poduptime.tests/"/>', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testApiJsonFormat()
  {
    $_GET['key']    = self::$api_key;
    $_GET['format'] = 'json';

    $output = $this->execTestFile();

    self::assertJson($output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testApiJsonpFormatWithCallback()
  {
    $_GET['key']      = self::$api_key;
    $_GET['format']   = 'json';
    $_GET['method']   = 'jsonp';
    $_GET['callback'] = 'cb_for_js';

    $output = $this->execTestFile();

    self::assertStringStartsWith('cb_for_js(', $output);
    self::assertJson(substr($output, strlen('cb_for_js('), -1));
  }
}
