<?php declare(strict_types = 1);

namespace Diasporg\Poduptime\Tests;

class GetTokenTest extends PoduptimeTestCase
{
  protected static $test_file = __DIR__ . '/../db/gettoken.php';

  /**
   * @runInSeparateProcess
   */
  public function testNoDomain()
  {
    $_POST['domain'] = '';

    $output = $this->execTestFile();

    self::assertEquals('no pod domain given', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testInvalidDomain()
  {
    $_POST['domain'] = 'very.much.non.existent';

    $output = $this->execTestFile();

    self::assertEquals('domain not found', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testNoEmail()
  {
    $_POST['domain'] = 'noemail.test.pod';

    $output = $this->execTestFile();

    self::assertEquals('domain is registered but no email associated, to add an email use the add a pod feature', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testEmailMismatch()
  {
    $_POST['domain'] = 'test.pod';
    $_POST['email']  = 'mismatched@email.address';

    $output = $this->execTestFile();

    self::assertEquals('email mismatch', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testTokenReviewSentToAdmin()
  {
    $_POST['domain'] = 'test.pod';
    $_POST['email']  = '';

    $output = $this->execTestFile();

    self::assertEquals('Link sent to administrator to review and verify, if approved they will forward the edit key to you.', $output);
  }

  /**
   * @runInSeparateProcess
   */
  public function testTokenSentToUser()
  {
    $_POST['domain'] = 'test.pod';
    $_POST['email']  = 'podmin@test.pod';

    $output = $this->execTestFile();

    self::assertEquals('Link sent to your email', $output);
  }

}
