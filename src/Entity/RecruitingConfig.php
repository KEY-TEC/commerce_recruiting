<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

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
 *     "list_builder" =
 *   "Drupal\commerce_recruitment\RecruitingConfigListBuilder",
 *     "views_data" = "Drupal\commerce_recruitment\RecruitingConfigViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_recruitment\Form\RecruitingConfigForm",
 *       "add" = "Drupal\commerce_recruitment\Form\RecruitingConfigForm",
 *       "edit" = "Drupal\commerce_recruitment\Form\RecruitingConfigForm",
 *       "delete" =
 *   "Drupal\commerce_recruitment\Form\RecruitingConfigDeleteForm",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_recruiting_config",
 *   data_table = "commerce_recruiting_config_field_data",
 *   admin_permission = "administer recruiting config entities",
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
 *   "/admin/commerce/recruitment/config/{commerce_recruiting_config}",
 *     "add-form" = "/admin/commerce/recruitment/config/add",
 *     "edit-form" =
 *   "/admin/commerce/recruitment/config/{commerce_recruiting_config}/edit",
 *     "delete-form" =
 *   "/admin/commerce/recruitment/config/{commerce_recruiting_config}/delete",
 *     "delete-multiple-form" = "/admin/commerce/recruitment/config/delete",
 *     "collection" = "/admin/commerce/recruitment/config",
 *   },
 * )
 */
class RecruitingConfig extends CommerceContentEntityBase implements RecruitingConfigInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  const RECRUIT_BONUS_METHOD_FIX = 'fix';

  const RECRUIT_BONUS_METHOD_PERCENT = 'percent';

  const RECRUIT_BONUS_METHOD_PERCENT_CART = 'percent_cart';

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
  public function getProduct() {
    return $this->get('product')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setProduct(ProductInterface $product) {
    $this->set('product', $product);
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setDescription(new TranslatableMarkup('The recruiting config name.'))
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
      ->setDescription(new TranslatableMarkup('Additional information about the recruitment.'))
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

    $fields['bonus'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(new TranslatableMarkup('Bonus'))
      ->setDescription(new TranslatableMarkup('A fix bonus value for the recruiter if fix bonus method is selected.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 2,
        'settings' => [],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['bonus_percent'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Bonus (%)'))
      ->setDescription(new TranslatableMarkup('Percentage bonus value of the product price for the recruiter if percentage bonus method is selected.'))
      ->setSettings([
        'min' => 0,
        'suffix' => '%',
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 2,
        'settings' => [],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['bonus_method'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Bonus Method'))
      ->setDescription(new TranslatableMarkup('Percentage bonus value of the product price for the recruiter.'))
      ->setSetting('allowed_values', [
        self::RECRUIT_BONUS_METHOD_FIX => 'Fix bonus',
        self::RECRUIT_BONUS_METHOD_PERCENT => 'Percentage bonus of product',
        self::RECRUIT_BONUS_METHOD_PERCENT_CART => 'Percentage bonus of cart (test)',
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 2,
        'settings' => [],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Start date'))
      ->setDescription(new TranslatableMarkup('The date the recruitment becomes available.'))
      ->setSetting('datetime_type', 'datetime')
      ->setDefaultValueCallback('Drupal\commerce_recruitment\Entity\RecruitingConfig::getDefaultStartDate')
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 3,
      ]);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('End date'))
      ->setDescription(new TranslatableMarkup('The date after which the recruitment is unavailable.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'datetime')
      ->setSetting('datetime_optional_label', t('Provide an end date'))
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 3,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setDescription(new TranslatableMarkup('Whether the recruiting config is enabled.'))
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

    $fields['product'] = BaseFieldDefinition::create('dynamic_entity_reference')
      ->setLabel(new TranslatableMarkup('Product'))
      ->setDescription(new TranslatableMarkup('The product or bundle for which someone will get the bonus after checkout.'))
      ->setSettings([
        'exclude_entity_types' => FALSE,
        'entity_type_ids' => [
          'commerce_product',
          'commerce_product_bundle',
        ],
      ])
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
    return gmdate(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $timestamp);
  }

}
