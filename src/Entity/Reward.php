<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the reward entity.
 *
 * @ingroup commerce_recruiting
 *
 * @ContentEntityType(
 *   id = "commerce_recruitment_reward",
 *   label = @Translation("Reward"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "list_builder" = "Drupal\commerce_recruiting\RewardListBuilder",
 *     "permission_provider" = "Drupal\entity\UncacheableEntityPermissionProvider",
 *     "access" = "Drupal\entity\UncacheableEntityAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_recruiting\Form\RecruitmentForm",
 *       "add" = "Drupal\commerce_recruiting\Form\RecruitmentForm",
 *       "edit" = "Drupal\commerce_recruiting\Form\RecruitmentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_recruiting\RecruitmentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_recruitment_reward",
 *   data_table = "commerce_recruitment_reward_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer reward entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" =
 *   "/user/rewards/{commerce_recruitment_reward}",
 *     "add-form" =
 *   "/user/rewards/add",
 *     "edit-form" =
 *   "/admin/commerce/recruitment/rewards/{commerce_recruitment_reward}/edit",
 *     "delete-form" =
 *   "/admin/commerce/recruitment/rewards/{commerce_recruitment_reward}/delete",
 *     "collection" = "/admin/commerce/recruitment/rewards",
 *   },
 *   field_ui_base_route = "commerce_recruitment_reward.settings"
 * )
 */
class Reward extends ContentEntityBase implements RewardInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
  use EntityOwnerTrait;

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
  public function getRecruitments() {
    return $this->get('recruitments')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstRecruitment() {
    $recruitments = $this->get('recruitments')->referencedEntities();
    return current($recruitments);
  }

  /**
   * {@inheritdoc}
   */
  public function setRecruitments(array $recruitments) {
    $this->set('recruitments', $recruitments);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRecruitments() {
    return !$this->get('recruitments')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addRecruitment(RecruitmentInterface $recruitment) {
    if (!$this->hasRecruitment($recruitment)) {
      $this->get('recruitments')->appendItem($recruitment);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeRecruitment(RecruitmentInterface $recruitment) {
    $index = $this->getRecruitmentIndex($recruitment);
    if ($index !== FALSE) {
      $this->get('recruitments')->offsetUnset($index);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRecruitment(RecruitmentInterface $recruitment) {
    return $this->getRecruitmentIndex($recruitment) !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimePeriod() {
    $time_period = [
      'from' => 0,
      'to' => 0,
    ];

    foreach ($this->getRecruitments() as $recruitment) {
      $created_timestamp = $recruitment->getCreatedTime();
      if ($created_timestamp < $time_period['from'] || $time_period['from'] == 0) {
        $time_period['from'] = $created_timestamp;
      }

      if ($created_timestamp < $time_period['to'] || $time_period['to'] == 0) {
        $time_period['to'] = $created_timestamp;
      }
    }

    return $time_period;
  }

  /**
   * Gets the index of the given order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment
   *   The order item.
   *
   * @return int|bool
   *   The index of the given order item, or FALSE if not found.
   */
  protected function getRecruitmentIndex(RecruitmentInterface $recruitment) {
    $values = $this->get('recruitments')->getValue();
    $recruitment_ids = array_map(function ($value) {
      return $value['target_id'];
    }, $values);

    return array_search($recruitment->id(), $recruitment_ids);
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

    $fields[$entity_type->getKey('owner')]
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the reward entity.'))
      ->setRevisionable(TRUE)
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
      ->setDescription(t('The name of the reward entity.'))
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

    $fields['recruitments'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Rewarded recruitments'))
      ->setDescription(t('The rewarded recruitments.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_recruitment')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 0,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => t('option'),
          'label_plural' => t('recruitments'),
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Bonus'))
      ->setDescription(t('A fix bonus value for the recruiter if fix bonus method is selected.'))
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
      ->setDescription(t('The recruitment state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setSetting('workflow', 'reward_default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the reward is published.'))
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

    /** @var \Drupal\commerce_recruiting\Entity\Recruitment $recruitment */
    foreach ($this->getRecruitments() as $recruitment) {
      if ($price == NULL) {
        $price = new Price($recruitment->getBonus()
          ->getNumber(), $recruitment->getBonus()->getCurrencyCode());
      }
      else {
        $price = $price->add($recruitment->getBonus());
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

    /** @var \Drupal\commerce_recruiting\Entity\Recruitment $recruitment */
    foreach ($this->getRecruitments() as $recruitment) {
      $recruitment->setState($this->getState()->getId());
      $recruitment->save();
    }
  }

}
