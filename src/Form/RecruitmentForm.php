<?php

namespace Drupal\commerce_recruiting\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for recruitment edit forms.
 *
 * @ingroup commerce_recruiting
 */
class RecruitmentForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\commerce_recruiting\Entity\Recruitment $entity */
    $form = parent::buildForm($form, $form_state);

    // Basefield descriptions are not shown in the form, so I add some of them
    // here again for better understanding of what they are doing. If you know
    // why that is and what to do about it, you're welcome to open an issue.
    if (isset($form['bonus_any_option'])) {
      $form['bonus_any_option']['widget']['value']['#description'] = $this->t('The recruiter can receive the bonus from any option of this campaign if bought by the customer. If this option is off, the recruiter can only receive the bonus of the product from the recruitment link.');
    }
    if (isset($form['bonus_quantity_multiplication'])) {
      $form['bonus_quantity_multiplication']['widget']['value']['#description'] = $this->t('The bonus will be multiplied by the quantity of the product in the order. If this option is off, the bonus will be applied only once.');
    }
    if (isset($form['auto_re_recruit'])) {
      $form['auto_re_recruit']['widget']['value']['#description'] = $this->t('This will create subsequent recruitments each time the customer orders one of the products below, if they have been recruited once before.');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created %label.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved %label.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.' . $entity->getEntityTypeId() . '.canonical', [$entity->getEntityTypeId() => $entity->id()]);
  }

}
