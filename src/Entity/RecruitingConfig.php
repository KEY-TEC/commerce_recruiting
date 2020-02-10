<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the recruitment config entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_recruiting_config",
 *   label = @Translation("Recruiting Configuration"),
 *   label_collection = @Translation("Recruiting configurations"),
 *   label_singular = @Translation("Recruiting configuration"),
 *   label_plural = @Translation("Recruiting configurations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count config",
 *     plural = "@count configs",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_recruitment\RecruitingConfigListBuilder",
 *     "views_data" = "Drupal\commerce_recruitment\RecruitingConfigViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "access" = "Drupal\commerce_recruitment\RecruitingConfigAccessControlHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_recruitment\Form\RecruitingConfigForm",
 *       "add" = "Drupal\commerce_recruitment\Form\RecruitingConfigForm",
 *       "edit" = "Drupal\commerce_recruitment\Form\RecruitingConfigForm",
 *       "delete" = "Drupal\commerce_recruitment\Form\RecruitingConfigDeleteForm",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_recruitment_config",
 *   data_table = "commerce_recruitment_field_data",
 *   admin_permission = "administer recruiting config entities",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "owner" = "recruiter",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/recruitment/config/{commerce_recruiting_config}",
 *     "add-form" = "/admin/commerce/recruitment/config/add",
 *     "edit-form" = "/admin/commerce/recruitment/config/{commerce_recruiting_config}/edit",
 *     "delete-form" = "/admin/commerce/recruitment/config/{commerce_recruiting_config}/delete",
 *     "delete-multiple-form" = "/admin/commerce/recruitment/config/delete",
 *     "collection" = "/admin/commerce/recruitment/config",
 *   },
 * )
 */
class RecruitingConfig extends ContentEntityBase implements RecruitingConfigInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->set('status', (bool) $enabled);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate($store_timezone = 'UTC') {
    return new DrupalDateTime($this->get('start_date')->value, $store_timezone);
  }

  /**
   * {@inheritdoc}
   */
  public function setStartDate(DrupalDateTime $start_date) {
    $this->get('start_date')->value = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate($store_timezone = 'UTC') {
    if (!$this->get('end_date')->isEmpty()) {
      return new DrupalDateTime($this->get('end_date')->value, $store_timezone);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setEndDate(DrupalDateTime $end_date = NULL) {
    $this->get('end_date')->value = NULL;
    if ($end_date) {
      $this->get('end_date')->value = $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBonus() {
    return $this->get('bonus')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBonus(Price $price) {
    return $this->set('bonus', $price);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return (int) $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields[$entity_type->getKey('owner')] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Recruiter'))
      ->setDescription(t('The recruiter (owner).'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The recruiting config name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('Additional information about the recruitment.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 1,
        'settings' => [
          'rows' => 3,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The date the recruitment becomes available.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDefaultValueCallback('Drupal\commerce_recruitment\Entity\RecruitingConfig::getDefaultStartDate')
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 5,
      ]);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The date after which the recruitment is unavailable.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'datetime')
      ->setSetting('datetime_optional_label', t('Provide an end date'))
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 6,
      ]);

    $fields['bonus'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Bonus'))
      ->setDescription(t('The bonus for the recruiter.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 5,
        'settings' => [],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the recruiting config is enabled.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'on_label' => t('Enabled'),
        'off_label' => t('Disabled'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 0,
      ]);

    /* @todo: fields to add:
     * - products (required)
     *    List of products that can apply for recruitment.
     * - promotion (optional)
     *    This recruitment uses the codes of the specified promotion.
    */
    return $fields;
  }

  /**
   * Default value callback for 'start_date' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return string
   *   The default value (date string).
   */
  public static function getDefaultStartDate() {
    $timestamp = \Drupal::time()->getRequestTime();
    return gmdate(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $timestamp);
  }

}
