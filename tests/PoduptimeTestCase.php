<?php declare(strict_types = 1);

namespace Diasporg\Poduptime\Tests;

use PHPUnit\Framework\TestCase;

class PoduptimeTestCase extends TestCase
{
  protected static $test_file;

  public function setUp()
  {
    $_SERVER['HTTP_HOST'] = 'poduptime.tests';
    parent::setUp();
  }

  protected function execTestFile()
  {
    if (!file_exists(static::$test_file)) {
      self::fail('test file not found');
    }

    ob_start();
    require static::$test_file;
    return ob_get_clean();
  }
}
