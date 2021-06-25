<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\Reward;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class RewardManager.
 */
class RewardManager implements RewardManagerInterface {

  /**
   * The recruitment manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentManagerInterface
   */
  protected $recruitmentManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler to invoke the alter hook with.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * RewardManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_recruiting\RecruitmentManagerInterface $recruitment_manager
   *   The recruitment manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RecruitmentManagerInterface $recruitment_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->recruitmentManager = $recruitment_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritDoc}
   */
  public function createReward(CampaignInterface $campaign, AccountInterface $recruiter) {
    /** @var \Drupal\commerce_recruiting\Entity\Reward $reward */
    $reward = Reward::create(['name' => $campaign->getName()]);
    $recruitments = $this->recruitmentManager->findRecruitmentsByCampaign($campaign, 'accepted', $recruiter);
    // Allow modules to alter the set of recruitments added to this reward.
    $this->moduleHandler->alter('recruitment_reward_recruitments', $recruitments, $campaign);

    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
    foreach ($recruitments as $recruitment) {
      $reward->addRecruitment($recruitment);
    }
    $reward->save();
    return $reward;
  }

  /**
   * {@inheritDoc}
   */
  public function findRewards(AccountInterface $recruiter) {
    return $this->entityTypeManager->getStorage('commerce_recruitment_reward')->loadByProperties(['user_id' => $recruiter->id()]);
  }
}
