<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the recruiting campaign entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_recruiting_campaign",
 *   label = @Translation("Recruiting Campaign Configuration"),
 *   label_collection = @Translation("Recruiting Campaign  configurations"),
 *   label_singular = @Translation("Recruiting Campaign configuration"),
 *   label_plural = @Translation("Recruiting Campaign configurations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count config",
 *     plural = "@count configs",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_recruiting\CampaignListBuilder",
 *     "views_data" = "Drupal\commerce_recruiting\CampaignViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "form" = {
 *       "default" = "Drupal\commerce_recruiting\Form\CampaignForm",
 *       "add" = "Drupal\commerce_recruiting\Form\CampaignForm",
 *       "edit" = "Drupal\commerce_recruiting\Form\CampaignForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_recruiting_campaign",
 *   data_table = "commerce_recruiting_campaign_field_data",
 *   admin_permission = "administer recruiting campaign entities",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "owner" = "owner",
 *   },
 *   links = {
 *     "canonical" =
 *   "/admin/commerce/recruiting/config/{commerce_recruiting_campaign}",
 *     "add-form" = "/admin/commerce/recruiting/config/add",
 *     "edit-form" =
 *   "/admin/commerce/recruiting/config/{commerce_recruiting_campaign}/edit",
 *     "delete-form" =
 *   "/admin/commerce/recruiting/config/{commerce_recruiting_campaign}/delete",
 *     "delete-multiple-form" = "/admin/commerce/recruiting/config/delete",
 *     "collection" = "/admin/commerce/recruiting/config",
 *   },
 * )
 */
class Campaign extends CommerceContentEntityBase implements CampaignInterface {

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
  public function getRecruiter() {
    return $this->get('recruiter')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecruiter(UserInterface $account) {
    $this->set('recruiter', $account);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPromotion() {
    return $this->get('promotion')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setPromotion(PromotionInterface $promotion) {
    $this->set('promotion', $promotion);
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
    $this->get('start_date')->value = $start_date->format(DateTimeItem::DATETIME_STORAGE_FORMAT);
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
      $this->get('end_date')->value = $end_date->format(DateTimeItem::DATETIME_STORAGE_FORMAT);
    }
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
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Ensure there's a back-reference on each option.
    foreach ($this->getOptions() as $option) {
      if ($option->campaign_id->isEmpty()) {
        $option->campaign_id = $this->id();
        $option->save();
      }
    }
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setDescription(new TranslatableMarkup('The recruiting campaign name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Description'))
      ->setDescription(new TranslatableMarkup('Additional information about the recruiting.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 1,
        'settings' => [
          'rows' => 1,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Start date'))
      ->setDescription(new TranslatableMarkup('The date the recruiting becomes available.'))
      ->setSetting('datetime_type', 'datetime')
      ->setDefaultValueCallback('Drupal\commerce_recruiting\Entity\Campaign::getDefaultStartDate')
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 3,
      ]);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('End date'))
      ->setDescription(new TranslatableMarkup('The date after which the recruiting is unavailable.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'datetime')
      ->setSetting('datetime_optional_label', t('Provide an end date'))
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 3,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDescription(new TranslatableMarkup('Whether the recruiting campaign is enabled.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE);

    $fields['recruiter'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Recruiter'))
      ->setDescription(new TranslatableMarkup('The recruiter.'))
      ->setSetting('target_type', 'user')
      ->setTranslatable($entity_type->isTranslatable())
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 4,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['promotion'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Promotion'))
      ->setDescription(new TranslatableMarkup('The user needs to use a coupon code from this promotion to apply.'))
      ->setSetting('target_type', 'commerce_promotion')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
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

    $fields['options'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Campaign options'))
      ->setDescription(t('The campaign option.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_recruiting_camp_option')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 0,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => t('option'),
          'label_plural' => t('options'),
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that this entity was last edited.'))
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that this entity was created.'));

    return $fields;
  }

  /**
   * Default value callback for 'start_date' base field definition.
   *
   * @return string
   *   The default value (date string).
   * @see ::baseFieldDefinitions()
   */
  public static function getDefaultStartDate() {
    $timestamp = \Drupal::time()->getRequestTime();
    return gmdate(DateTimeItem::DATETIME_STORAGE_FORMAT, $timestamp);
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->get('options')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstOption() {
    $options = $this->get('options')->referencedEntities();
    return current($options);
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->set('options', $options);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasOptions() {
    return !$this->get('options')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addOption(CampaignOptionInterface $option) {
    if (!$this->hasOption($option)) {
      $this->get('options')->appendItem($option);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeOption(CampaignOptionInterface $option) {
    $index = $this->getOptionIndex($option);
    if ($index !== FALSE) {
      $this->get('options')->offsetUnset($index);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasOption(CampaignOptionInterface $option) {
    return $this->getOptionIndex($option) !== FALSE;
  }

  /**
   * Gets the index of the given order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The order item.
   *
   * @return int|bool
   *   The index of the given order item, or FALSE if not found.
   */
  protected function getOptionIndex(CampaignOptionInterface $option) {
    $values = $this->get('options')->getValue();
    $option_ids = array_map(function ($value) {
      return $value['target_id'];
    }, $values);

    return array_search($option->id(), $option_ids);
  }

}
