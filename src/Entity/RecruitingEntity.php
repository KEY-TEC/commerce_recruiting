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
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "recruiter",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/recruitment/recruiting/{commerce_recruiting}",
 *     "add-page" = "/admin/commerce/recruitment/recruiting/add",
 *     "add-form" = "/admin/commerce/recruitment/recruiting/add/{commerce_recruiting_type}",
 *     "edit-form" = "/admin/commerce/recruitment/recruiting/{commerce_recruiting}/edit",
 *     "delete-form" = "/admin/commerce/recruitment/recruiting/{commerce_recruiting}/delete",
 *     "collection" = "/admin/commerce/recruitment/recruiting",
 *   },
 *   bundle_entity_type = "commerce_recruiting_type",
 *   field_ui_base_route = "entity.commerce_recruiting_type.edit_form"
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
  public function isPaidOut() {
    return $this->get('is_paid_out')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaidOut($is_paid_out) {
    return $this->set('is_paid_out', $is_paid_out);
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

    $fields['product'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Product'))
      ->setDescription(t('The recommended product.'))
      ->setSetting('target_type', 'commerce_product')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_entity_view',
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

    //$fields['status']->setDescription(t('A boolean indicating whether the Recruiting entity is published.'))
    //  ->setDisplayOptions('form', [
    //    'type' => 'boolean_checkbox',
    //    'weight' => -3,
    //  ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['is_paid_out'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Paid out'))
      ->setDescription(t('A boolean indicating if the bonus has been paid out to the recruiter.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
