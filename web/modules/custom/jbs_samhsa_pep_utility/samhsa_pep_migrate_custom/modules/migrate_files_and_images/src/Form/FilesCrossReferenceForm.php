<?php

/**
 * @file
 * Contains \Drupal\migrate_files_and_images\Form\FilesCrossReferenceForm.
 */

namespace Drupal\migrate_files_and_images\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Files cross reference edit forms.
 *
 * @ingroup migrate_files_and_images
 */
class FilesCrossReferenceForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\migrate_files_and_images\Entity\FilesCrossReference */
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
        \Drupal::messenger()->addMessage($this->t('Created the %label Files cross reference.', [
            '%label' => $entity->label(),
        ]));
        //drupal_set_message($this->t('Created the %label Files cross reference.', [
        //  '%label' => $entity->label(),
        //]));
        break;

      default:
          \Drupal::messenger()->addMessage($this->t('Saved the %label Files cross reference.', [
              '%label' => $entity->label(),
          ]));

          //drupal_set_message($this->t('Saved the %label Files cross reference.', [
          //'%label' => $entity->label(),
        //]));
    }
    $form_state->setRedirect('entity.files_cross_reference.canonical', ['files_cross_reference' => $entity->id()]);
  }

}
