<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\Tests\commerce_recruiting\Traits\RecruitmentEntityCreationTrait;

/**
 * RewardManagerTest.
 *
 * @group commerce_recruiting
 */
class RewardManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitmentEntityCreationTrait;

  /**
   * Test createReward.
   */
  public function testCreateReward() {
    $recruiter = $this->createUser();
    $campaign = $this->createCampaign($recruiter);
    $recruited = $this->createUser();
    $products = [];
    $products[] = $this->createProduct();
    $products[] = $this->createProduct();
    $recruitments = $this->createRecruitings($campaign, $recruiter, $recruited, $this->createOrder($products));
    $this->assertEqual(count($recruitments), 2);
    $this->recruitmentManager->applyTransitions('accept');
    $reward = $this->rewardManager->createReward($campaign, $recruiter);
    $this->assertEqual(count($reward->getRecruitments()), 2);
    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
    foreach ($reward->getRecruitments() as $recruitment) {
      $this->assertEqual($recruitment->getState()->getId(), 'paid_pending');
    }
    $reward->setState('paid');
    $reward->save();
    foreach ($reward->getRecruitments() as $recruitment) {
      $this->assertEqual($recruitment->getState()->getId(), 'paid');
    }
    $this->assertEqual(20.000000, $reward->getPrice()->getNumber());

  }

}
