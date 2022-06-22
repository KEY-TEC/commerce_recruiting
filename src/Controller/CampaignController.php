<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for campaign related pages.
 */
class CampaignController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function recruitersListPage(CampaignInterface $commerce_recruitment_campaign) {
    $campaign_option_storage = $this->entityTypeManager()->getStorage('commerce_recruitment_camp_option');
    $options = $campaign_option_storage->loadByProperties(['campaign_id' => $commerce_recruitment_campaign->id()]);
    $options = array_keys($options);
    $recruitments_storage = $this->entityTypeManager()->getStorage('commerce_recruitment');
    $query = $recruitments_storage->getQuery();
    $query->condition('campaign_option', $options, 'IN');
    $recruitments = $recruitments_storage->loadMultiple($query->execute());

    $users = [];
    $products = [];
    $data = [];
    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
    foreach ($recruitments as $recruitment) {
      $users[$recruitment->getOwnerId()] = $recruitment->getOwner()->getEmail();
      $product = $recruitment->getProduct();
      $product_id = $product->bundle() . '_' . $product->id();
      $products[$product_id] = $product->label();

      if (!isset($data[$recruitment->getOwnerId()][$product_id])) {
        $data[$recruitment->getOwnerId()][$product_id] = 0;
      }
      $data[$recruitment->getOwnerId()][$product_id]++;
    }

    $header = [$this->t('Account')];
    foreach ($products as $product) {
      $header[] = $product;
    }

    $rows = [];
    foreach ($data as $user_id => $user_data) {
      $row = [$users[$user_id]];
      foreach ($products as $product_id => $product) {
        $row[] = $user_data[$product_id] ?? 0;
      }

      $rows[] = $row;
    }

    usort($rows, function ($x, $y) {
      $count_x = 0;
      $count_y = 0;
      foreach ($x as $key => $col) {
        if ($key === 0) {
          continue;
        }
        $count_x += $col;
      }
      foreach ($y as $key => $col) {
        if ($key === 0) {
          continue;
        }
        $count_y += $col;
      }

      if ($count_x === $count_y) {
        return 0;
      }
      return $count_x < $count_y ? 1 : -1;
    });

    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

}
