<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;

/**
 * Defines the Invoice entity.
 *
 * @ingroup commerce_recruiting
 *
 * @ContentEntityType(
 *   id = "commerce_recruiting_invoice",
 *   label = @Translation("Invoice"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_recruiting\InvoiceListBuilder",
 *     "views_data" = "Drupal\commerce_recruiting\Entity\InvoiceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_recruiting\Form\InvoiceForm",
 *       "add" = "Drupal\commerce_recruiting\Form\InvoiceForm",
 *       "edit" = "Drupal\commerce_recruiting\Form\InvoiceForm",
 *       "delete" = "Drupal\commerce_recruiting\Form\InvoiceDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_recruiting\InvoiceHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\commerce_recruiting\InvoiceAccessControlHandler",
 *   },
 *   base_table = "commerce_recruiting_invoice",
 *   translatable = FALSE,
 *   admin_permission = "administer invoice entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" =
 *   "/user/invoice/{commerce_recruiting_invoice}",
 *     "add-form" =
 *   "/user/invoice/add",
 *     "edit-form" =
 *   "/admin/commerce/recruiting/commerce_recruiting_invoice/{commerce_recruiting_invoice}/edit",
 *     "delete-form" =
 *   "/admin/commerce/recruiting/commerce_recruiting_invoice/{commerce_recruiting_invoice}/delete",
 *     "collection" = "/admin/commerce/recruiting/commerce_recruiting_invoice",
 *   },
 *   field_ui_base_route = "commerce_recruiting_invoice.settings"
 * )
 */
class Invoice extends ContentEntityBase implements InvoiceInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    if (!$this->get('price')->isEmpty()) {
      return $this->get('price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    return $this->set('price', $price);
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

  /**
   * {@inheritdoc}
   */
  public function getRecruitings() {
    return $this->get('recruitings')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstRecruiting() {
    $recrutings = $this->get('recruitings')->referencedEntities();
    return current($recrutings);
  }

  /**
   * {@inheritdoc}
   */
  public function setRecruitings(array $recrutings) {
    $this->set('recruitings', $recrutings);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRecruitings() {
    return !$this->get('recruitings')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addRecruiting(RecruitingInterface $recruting) {
    if (!$this->hasRecruiting($recruting)) {
      $this->get('recruitings')->appendItem($recruting);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeRecruiting(RecruitingInterface $recruting) {
    $index = $this->getRecruitingIndex($recruting);
    if ($index !== FALSE) {
      $this->get('recruitings')->offsetUnset($index);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRecruiting(RecruitingInterface $recruting) {
    return $this->getRecruitingIndex($recruting) !== FALSE;
  }

  /**
   * Gets the index of the given order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruting
   *   The order item.
   *
   * @return int|bool
   *   The index of the given order item, or FALSE if not found.
   */
  protected function getRecruitingIndex(RecruitingInterface $recruting) {
    $values = $this->get('recruitings')->getValue();
    $recruting_ids = array_map(function ($value) {
      return $value['target_id'];
    }, $values);

    return array_search($recruting->id(), $recruting_ids);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Invoice entity.'))
      ->setRevisionable(TRUE)
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
      ->setDescription(t('The name of the Invoice entity.'))
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

    $fields['recruitings'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Invoiced recruitings'))
      ->setDescription(t('The Invoiced recruitings.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_recruiting')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 0,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => t('option'),
          'label_plural' => t('recruitings'),
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('commerce_price')
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

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The recruiting state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setSetting('workflow', 'invoice_default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Invoice is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

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
  public function preSave(EntityStorageInterface $storage, $update = TRUE) {
    /** @var \Drupal\commerce_price\Price $price */
    $price = NULL;

    /** @var \Drupal\commerce_recruiting\Entity\Recruiting $recruiting */
    foreach ($this->getRecruitings() as $recruiting) {
      if ($price == NULL) {
        $price = new Price($recruiting->getBonus()
          ->getNumber(), $recruiting->getBonus()->getCurrencyCode());
      }
      else {
        $price = $price->add($recruiting->getBonus());
      }
    }
    if ($price != NULL) {
      $this->setPrice($price);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    /** @var \Drupal\commerce_price\Price $price */
    $price = NULL;

    /** @var \Drupal\commerce_recruiting\Entity\Recruiting $recruiting */
    foreach ($this->getRecruitings() as $recruiting) {
      $recruiting->setState($this->getState()->getId());
      $recruiting->save();
    }
  }

}
