<?php

namespace Drupal\commerce_recruitment\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RecruitingEntityTypeForm.
 */
class RecruitingEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $recruiting_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $recruiting_type->label(),
      '#description' => $this->t("Label for the Recruiting entity type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $recruiting_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_recruitment\Entity\RecruitingEntityType::load',
      ],
      '#disabled' => !$recruiting_type->isNew(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $recruiting_type = $this->entity;
    $status = $recruiting_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Recruiting entity type.', [
          '%label' => $recruiting_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Recruiting entity type.', [
          '%label' => $recruiting_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($recruiting_type->toUrl('collection'));
  }

}
