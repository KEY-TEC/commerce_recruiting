<?php

namespace Drupal\commerce_recruiting\Plugin\Commerce\Condition;

use Drupal\commerce\EntityUuidMapperInterface;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\commerce_recruiting\RecruitmentManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the recruitment campaign condition.
 *
 * @CommerceCondition(
 *   id = "recruitment_campaign",
 *   label = @Translation("Recruitment Campaign"),
 *   display_label = @Translation("The user was recommended by a recruitment campaign url and has at least one of those products"),
 *   category = @Translation("Recruitment"),
 *   entity_type = "commerce_order",
 *   weight = 1,
 * )
 */
class RecruitmentCampaignCondition extends ConditionBase implements ContainerFactoryPluginInterface {

  use RecruitmentCampaignConditionTrait;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The recruitment manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentManagerInterface
   */
  protected $recruitmentManager;

  /**
   * RecruitmentCampaignCondition constructor.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce\EntityUuidMapperInterface $entity_uuid_mapper
   *   The entity UUID mapper.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\commerce_recruiting\RecruitmentManagerInterface $recruitment_manager
   *   The recruitment manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityUuidMapperInterface $entity_uuid_mapper, RouteMatchInterface $route_match, RecruitmentManagerInterface $recruitment_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->recruitmentCampaignStorage = $entity_type_manager->getStorage('commerce_recruitment_campaign');
    $this->entityUuidMapper = $entity_uuid_mapper;
    $this->routeMatch = $route_match;
    $this->recruitmentManager = $recruitment_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('commerce.entity_uuid_mapper'),
      $container->get('current_route_match'),
      $container->get('commerce_recruiting.recruitment_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $campaigns_ids = $this->getCampaignIds();
    $matches = $this->recruitmentManager->sessionMatch($order);

    if (!empty($matches)) {
      foreach ($matches as $match) {
        if (!empty($match['campaign_option']) &&  in_array($match['campaign_option']->getCampaign()->id(), $campaigns_ids)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
