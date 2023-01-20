<?php

namespace Drupal\commerce_recruiting\Plugin\Field\FieldType;

use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\user\UserInterface;

/**
 * Plugin implementation of the 'recruitment_info' field type.
 *
 * @FieldType(
 *   id = "recruitment_info",
 *   label = @Translation("Recruiting Info"),
 *   category = @Translation("Commerce Recruiting"),
 *   description = @Translation("A field to store recruitment information"),
 *   no_ui = TRUE,
 * )
 */
class RecruitmentInfo extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {
    return [
      'columns' => [
        'campaign_option_target_id' => [
          'description' => 'The ID of the campaign option.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'recruiter_target_id' => [
          'description' => 'The ID of the recruiter.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'number' => [
          'description' => 'The bonus number.',
          'type' => 'numeric',
          'precision' => 19,
          'scale' => 6,
        ],
        'currency_code' => [
          'description' => 'The bonus currency code.',
          'type' => 'varchar',
          'length' => 3,
        ],
      ],
      'indexes' => [
        'campaign_option_target_id' => ['campaign_option_target_id'],
        'recruiter_target_id' => ['recruiter_target_id'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName(): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $properties = [];

    $properties['campaign_option_target_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('@label ID', ['@label' => 'Campaign option']))
      ->setSetting('unsigned', TRUE);
    $properties['campaign_option'] = DataReferenceDefinition::create('entity')
      ->setLabel('Campaign option')
      ->setDescription(new TranslatableMarkup('The campaign option'))
      ->setComputed(TRUE)
      ->setReadOnly(FALSE)
      ->setTargetDefinition(EntityDataDefinition::create('commerce_recruitment_camp_option'))
      ->addConstraint('EntityType', 'commerce_recruitment_camp_option');

    $properties['recruiter_target_id'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('@label ID', ['@label' => 'Recruiter']))
      ->setSetting('unsigned', TRUE);
    $properties['recruiter'] = DataReferenceDefinition::create('entity')
      ->setLabel('Recruiter')
      ->setDescription(new TranslatableMarkup('The recruiter'))
      ->setComputed(TRUE)
      ->setReadOnly(FALSE)
      ->setTargetDefinition(EntityDataDefinition::create('user'))
      ->addConstraint('EntityType', 'user');

    $properties['number'] = DataDefinition::create('string')
      ->setLabel(t('Number'))
      ->setRequired(FALSE);
    $properties['currency_code'] = DataDefinition::create('string')
      ->setLabel(t('Currency code'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * Returns the campaign option.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface|null
   *   The campaign option.
   */
  public function getCampaignOption(): ?CampaignOptionInterface {
    return $this->campaign_option;
  }

  /**
   * Returns the recruiter.
   *
   * @return \Drupal\user\UserInterface|null
   *   The recruiter.
   */
  public function getRecruiter(): ?UserInterface {
    return $this->recruiter;
  }

  /**
   * Returns the bonus value object.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The Price value object.
   */
  public function getBonus(): ?Price {
    if (empty($this->currency_code)) {
      // Cannot create price without currency.
      return NULL;
    }

    if (empty($this->number)) {
      $this->number = 0;
    }
    return new Price($this->number, $this->currency_code);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE): void {
    parent::setValue($values, FALSE);
    foreach ($this->getReferenceProperties() as $property) {
      $property_target_id = $property . '_target_id';

      // @see EntityReferenceItem::setValue.
      if (is_array($values) && array_key_exists($property_target_id, $values) && !isset($values[$property])) {
        $this->onChange($property_target_id, FALSE);
      }
      elseif (is_array($values) && !array_key_exists($property_target_id, $values) && isset($values[$property])) {
        $this->onChange($property, FALSE);
      }
      elseif (is_array($values) && array_key_exists($property_target_id, $values) && isset($values[$property])) {
        $entity_id = $this->get($property)->getTargetIdentifier();
        if (!$this->$property->isNew() && $values[$property_target_id] !== NULL && ($entity_id != $values[$property_target_id])) {
          throw new \InvalidArgumentException('The target id and entity passed to the entity reference item do not match.');
        }
      }
      if ($notify && $this->parent) {
        $this->parent->onChange($this->getName());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $values = parent::getValue();

    foreach ($this->getReferenceProperties() as $property) {
      if ($this->hasNewEntity($property)) {
        $values[$property] = $this->$property;
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE): void {
    // Make sure that the target ID and the target property stay in sync.
    foreach ($this->getReferenceProperties() as $field_property_name) {
      $property_target_id = $field_property_name . '_target_id';

      if ($property_name == $field_property_name) {
        $property = $this->get($field_property_name);
        $target_id = $property->isTargetNew() ? NULL : $property->getTargetIdentifier();
        $this->writePropertyValue($property_target_id, $target_id);
      }
      elseif ($property_name == $property_target_id) {
        $this->writePropertyValue($field_property_name, $this->$property_target_id);
      }
      parent::onChange($property_name, $notify);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(): void {
    foreach ($this->getReferenceProperties() as $property) {
      $property_target_id = $property . '_target_id';
      if ($this->hasNewEntity($property)) {
        // Save the entity if it has not already been saved by some other code.
        if ($this->$property->isNew()) {
          $this->$property->save();
        }
        // Make sure the parent knows we are updating this property so it can
        // react properly.
        $this->$property_target_id = $this->entity->id();
      }
      if (!$this->isEmpty() && $this->$property_target_id === NULL) {
        $this->$property_target_id = $this->$property->id();
      }
    }
  }

  /**
   * Determines whether the item holds an unsaved entity.
   *
   * @param string $property
   *   The property to check.
   *
   * @return bool
   *   TRUE if the item holds an unsaved entity.
   */
  public function hasNewEntity(string $property): bool {
    $target_id_name = $property . '_target_id';
    return !$this->isEmpty() && $this->$target_id_name === NULL && $this->$property->isNew();
  }

  /**
   * Returns the reference properties.
   *
   * @return array
   *   The reference properties of this field type.
   */
  protected function getReferenceProperties(): array {
    return ['campaign_option', 'recruiter'];
  }

}
