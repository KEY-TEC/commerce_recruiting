<?php

namespace Drupal\commerce_recruitment\Tests\Kernel\Controller;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides automated tests for the commerce_recruitment module.
 */
class RecruitingCodeControllerTest extends WebTestBase {

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "commerce_recruitment RecruitingCodeController's controller functionality",
      'description' => 'Test Unit for module commerce_recruitment and controller RecruitingCodeController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests commerce_recruitment functionality.
   */
  public function testRecruitingCodeController() {
    // Check that the basic functions of module commerce_recruitment.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
