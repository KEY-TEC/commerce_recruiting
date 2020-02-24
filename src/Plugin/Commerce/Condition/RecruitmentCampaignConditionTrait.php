<?php

namespace Drupal\commerce_recruiting\Plugin\Commerce\Condition;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides common configuration for the recruitment campaign conditions.
 */
trait RecruitmentCampaignConditionTrait {

  /**
   * The campaign storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $recruitmentCampaignStorage;

  /**
   * The entity UUID mapper.
   *
   * @var \Drupal\commerce\EntityUuidMapperInterface
   */
  protected $entityUuidMapper;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'recruitment_campaigns' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $campaigns = NULL;
    $rcids = $this->getCampaignIds();

    if (!empty($rcids)) {
      $campaigns = $this->recruitmentCampaignStorage->loadMultiple($rcids);
    }

    $form['recruitment_campaigns'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Recruitment Campaigns'),
      '#default_value' => $campaigns,
      '#target_type' => 'commerce_recruitment_campaign',
      '#tags' => TRUE,
      '#required' => TRUE,
      '#maxlength' => NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    // Convert selected IDs into UUIDs, and store them.
    $values = $form_state->getValue($form['#parents']);
    $rcids = array_column($values['recruitment_campaigns'], 'target_id');
    $campaign_uuids = $this->entityUuidMapper->mapFromIds('commerce_recruitment_campaign', $rcids);
    $this->configuration['recruitment_campaigns'] = [];
    foreach ($campaign_uuids as $uuid) {
      $this->configuration['recruitment_campaigns'][] = [
        'campaign' => $uuid,
      ];
    }
  }

  /**
   * Gets the configured campaign IDs.
   *
   * @return array
   *   The campaign IDs.
   */
  protected function getCampaignIds() {
    // Map the UUIDs.
    $campaign_uuids = array_column($this->configuration['recruitment_campaigns'], 'campaign');
    return $this->entityUuidMapper->mapToIds('commerce_recruitment_campaign', $campaign_uuids);
  }

}
