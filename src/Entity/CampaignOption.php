<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\link\LinkItemInterface;
use Drupal\user\UserInterface;
use http\Exception\InvalidArgumentException;

/**
 * Defines the campaign option entity.
 *
 * @ingroup commerce_recruiting
 *
 * @ContentEntityType(
 *   id = "commerce_recruitment_camp_option",
 *   label = @Translation("Campaign option"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
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
 *   base_table = "commerce_recruitment_camp_option",
 *   translatable = FALSE,
 *   admin_permission = "administer recruitment option entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "code",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   field_ui_base_route = "commerce_recruitment_camp_option.settings"
 * )
 */
class CampaignOption extends ContentEntityBase implements CampaignOptionInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getCode() {
    return $this->get('code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCode($code) {
    $this->set('code', $code);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    /** @var \CommerceGuys\Intl\Formatter\CurrencyFormatterInterface $formatter */
    $formatter = \Drupal::service('commerce_price.currency_formatter');
    $label = 'Code: ' . $this->getCode() . ' ';
    $label .= $this->getProduct() != NULL ? '(Product: ' . $this->getProduct()
      ->label() . ') ' : ' ';

    if ($this->getBonusMethod() == CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX) {
      $bonus = $this->getBonus() != NULL ? $formatter->format($this->getBonus()->getNumber(), $this->getBonus()->getCurrencyCode()) : '0';
      $label .= '- Bonus: ' . $bonus;
    }
    else {
      $label .= '- Bonus: ' . $this->getBonusPercent() . '%';
    }

    return $label;
  }

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
  public function getCampaign() {
    return $this->get('campaign_id')->entity;
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
  public function getBonusPercent() {
    return $this->get('bonus_percent')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBonusPercent(int $percent) {
    $this->set('bonus_percent', $percent);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBonus() {
    if (!$this->get('bonus')->isEmpty()) {
      return $this->get('bonus')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setBonus(Price $price) {
    $this->set('bonus', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBonusMethod() {
    return $this->get('bonus_method')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setProduct(EntityInterface $product) {
    $this->set('product', $product);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Code'))
      ->setDescription(t('The campaign option code.'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 50,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValueCallback('Drupal\commerce_recruiting\Entity\CampaignOption::getDefaultCode')
      ->addConstraint('CodeUnique');

    // The order backreference, populated by Campaign::postSave().
    $fields['campaign_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Campaign'))
      ->setDescription(t('The parent campaign.'))
      ->setSetting('target_type', 'commerce_recruitment_campaign')
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the campaign option entity.'))
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

    $fields['status']->setDescription(t('A boolean indicating whether the campaign option is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['bonus'] = BaseFieldDefinition::create('commerce_price')
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

    $fields['bonus_percent'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Bonus (%)'))
      ->setDescription(t('Percentage bonus value of the product price for the recruiter if percentage bonus method is selected.'))
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

    $fields['product'] = BaseFieldDefinition::create('dynamic_entity_reference')
      ->setLabel(t('Product'))
      ->setCardinality(1)
      ->setDescription(t('The product or bundle for which someone will get the bonus after checkout.'))
      ->setRequired(TRUE)
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

    $fields['bonus_method'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Bonus Method'))
      ->setDescription(t('Percentage bonus value of the product price for the recruiter.'))
      ->setSetting('allowed_values', [
        self::RECRUIT_BONUS_METHOD_FIX => 'Fix bonus',
        self::RECRUIT_BONUS_METHOD_PERCENT => 'Percentage bonus of product',
      ])
      ->setDefaultValue(self::RECRUIT_BONUS_METHOD_FIX)
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

    $fields['redirect'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Redirect'))
      ->setDescription(t("Page to redirect to, when using this code's link. Leave empty to go to the product page."))
      ->setTranslatable(TRUE)
      ->setSettings([
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_DISABLED,
      ])
      ->setDisplayOptions('form', [
        'type' => 'link',
        'weight' => 3,
      ])
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
   * Default value callback for 'code' base field definition.
   *
   * @return string
   *   A generated short ID.
   *
   * @see https://github.com/crisu83/php-shortid
   */
  public static function getDefaultCode() {
    do {
      // https://stackoverflow.com/questions/4570980/generating-a-random-code-in-php#comment107472646_4571013
      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $i = 0;
      $code = '';
      while ($i <= 6) {
        $num = \random_int(0, strlen($chars) - 1);
        $code .= $chars[$num];
        $i++;
      }

      $query = \Drupal::entityTypeManager()->getStorage('commerce_recruitment_camp_option')
        ->getQuery();
      $query->condition('status', 1);
      $query->condition('code', $code);
      $rcoids = $query->execute();
    } while (!empty($rcoids));

    return $code;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateBonus(OrderItemInterface $order_item) {
    if ($this->getBonusMethod() == CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX) {
      return $this->getBonus();
    }
    elseif ($this->getBonusMethod() == CampaignOptionInterface::RECRUIT_BONUS_METHOD_PERCENT) {
      if ($order_item->getTotalPrice() === NULL) {
        // Re-set unit price to calculate the total price
        // in case where the total price missing yet.
        $order_item->setUnitPrice($order_item->getUnitPrice());
      }
      $total_price = $order_item->getTotalPrice()->getNumber();
      $bonus = $total_price / 100 * $this->getBonusPercent();
      return new Price($bonus, $order_item->getTotalPrice()->getCurrencyCode());
    }
    else {
      throw new InvalidArgumentException("No valid bonus method selected. Method: '" . $this->getBonusMethod() . "'");
    }
  }

}
