<?php

/**
 * @file
 * Provide views data for the Order module.
 */

/**
 * Implements hook_views_data().
 */
function commerce_recruiting_views_data() {
  $data['views']['recruit_a_friend_bonus_total'] = [
    'title' => t('Bonus total'),
    'help' => t('Displays the bonus total field, requires an Order ID argument.'),
    'area' => [
      'id' => 'recruit_a_friend_bonus_total',
    ],
  ];
  return $data;
}
