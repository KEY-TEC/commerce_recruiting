<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Recruiting entity entity.
 *
 * @ingroup commerce_recruitment
 *
 * @ContentEntityType(
 *   id = "commerce_recruiting",
 *   label = @Translation("Recruiting"),
 *   bundle_label = @Translation("Recruitings"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_recruitment\RecruitingEntityListBuilder",
 *     "views_data" = "Drupal\commerce_recruitment\RecruitingEntityViewsData",
 *     "translation" = "Drupal\commerce_recruitment\RecruitingEntityTranslationHandler",
 *     "access" = "Drupal\commerce_recruitment\RecruitingEntityAccessControlHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_recruitment\Form\RecruitingEntityForm",
 *       "add" = "Drupal\commerce_recruitment\Form\RecruitingEntityForm",
 *       "edit" = "Drupal\commerce_recruitment\Form\RecruitingEntityForm",
 *       "delete" = "Drupal\commerce_recruitment\Form\RecruitingEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_recruitment\RecruitingEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_recruitment",
 *   data_table = "commerce_recruitment_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer recruiting entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "recruiter",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/recruitment/recruiting/{commerce_recruiting}",
 *     "add-form" = "/admin/commerce/recruitment/recruiting/add",
 *     "edit-form" = "/admin/commerce/recruitment/recruiting/{commerce_recruiting}/edit",
 *     "delete-form" = "/admin/commerce/recruitment/recruiting/{commerce_recruiting}/delete",
 *     "collection" = "/admin/commerce/recruitment/recruiting",
 *   },
 * )
 */
class RecruitingEntity extends ContentEntityBase implements RecruitingEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
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
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecruited() {
    return $this->get('recruited')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecruited(UserInterface $account) {
    $this->set('recruited', $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecruitedId() {
    return $this->get('recruited')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecruitedId($uid) {
    $this->set('recruited', $uid);
  }

  /**
   * {@inheritdoc}
   */
  public function getProduct() {
    return $this->get('product')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setProduct(ProductInterface $product) {
    return $this->set('product', $product);
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    // Add ownership fields.
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
      ->setDescription(t('The name of the recruiting entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['recruiting_config'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Recruiting config'))
      ->setDescription(t('The recruiting config.'))
      ->setSetting('target_type', 'commerce_recruiting_config')
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

    $fields['recruited'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Recruited user'))
      ->setDescription(t('The recruited user.'))
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

    $fields['order_item'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order item'))
      ->setDescription(t('The source order item.'))
      ->setSetting('target_type', 'commerce_order_item')
      ->setSetting('handler', 'default')
      ->setSetting('display_description', TRUE)
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

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The recruiting state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setSetting('workflow', 'recruiting_default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItem() {
    return $this->get('order_item')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    /** @var \Drupal\commerce_order\Entity\OrderItem $order_item */
    $order_item = $this->getOrderItem();
    if ($order_item != NULL) {
      return $order_item->getOrder();
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->first();
  }

  /**
   * {@inheritdoc}
   */
  public function setState($state_id) {
    $this->set('state', $state_id);
    return $this;
  }

}
