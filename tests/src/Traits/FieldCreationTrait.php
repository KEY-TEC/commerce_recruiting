<?php

namespace Drupal\Tests\commerce_recruiting\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides methods to create simple fields.
 *
 * This trait is meant to be used only by test classes.
 */
trait FieldCreationTrait {

  /**
   * Create simple test fields.
   */
  protected function createSimpleField($entity_type, $bundle, $field_name, $type, $cardinality = 1, $storage_settings = []) {
    $field_config = FieldStorageConfig::load($entity_type . '.' . $field_name);
    if (empty($field_config)) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => $type,
        'cardinality' => $cardinality,
      ] + $storage_settings);
      $field_storage->save();
    }

    $field = FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
    ]);
    $field->save();
  }

}
