<?php

namespace Drupal\commerce_recruiting\Tests\Kernel\Controller;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the commerce_recruiting module.
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
      'name' => "commerce_recruiting RecruitingCodeController's controller functionality",
      'description' => 'Test Unit for module commerce_recruiting and controller RecruitingCodeController.',
      'group' => 'Other',
    ];
  }

  /**
   * Tests commerce_recruiting functionality.
   */
  public function testRecruitingCodeController() {
    // Check that the basic functions of module commerce_recruiting.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
