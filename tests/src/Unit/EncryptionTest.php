<?php

namespace Drupal\Tests\commerce_recruiting\Unit;

use Drupal\commerce_recruitment\Encryption;
use Drupal\Tests\token\Kernel\UnitTest;

/**
 * Class EncryptionTest.
 */
class EncryptionTest extends UnitTest {

  /**
   * Testvariable.
   *
   * @var string
   */
  protected $testString = "10;12";

  /**
   * Test encryption.
   */
  public function testEncryption() {
    $encrypted = Encryption::encrypt($this->testString);
    $this->assertEqual("MTA7MTI!3D", $encrypted);
    $this->assertNotEqual($this->testString, $encrypted);

    $decrypted = Encryption::decrypt($encrypted);
    $this->assert($this->testString, $decrypted);
  }

}
