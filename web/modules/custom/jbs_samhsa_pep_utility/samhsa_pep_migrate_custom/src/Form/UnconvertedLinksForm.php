<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_migrate_custom\Form\UnconvertedLinksForm.
 */

namespace Drupal\samhsa_pep_migrate_custom\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Unconverted links edit forms.
 *
 * @ingroup samhsa_pep_migrate_custom
 */
class UnconvertedLinksForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinks */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

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
        \Drupal::messenger()->addMessage($this->t('Created the %label Unconverted links.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved the %label Unconverted links.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.unconverted_links.canonical', ['unconverted_links' => $entity->id()]);
  }

}
