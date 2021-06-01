<?php

namespace Drupal\samhsa_term_elevation\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for SAHMSA Term Elevation edit forms.
 *
 * @ingroup samhsa_term_elevation
 */
class SamhsaTermElevationForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\samhsa_term_elevation\Entity\SamhsaTermElevation $entity */
    $form = parent::buildForm($form, $form_state);
    for ($i = 0; $i <= $form['elnid']['widget']['#max_delta']; $i++) {
      $form['elnid']['widget'][$i]['value']['#type'] = 'textfield';
      $form['elnid']['widget'][$i]['value']['#autocomplete_route_name'] = 'samhsa_term_elevation.matcher';
    }
    for ($i = 0; $i <= $form['exnid']['widget']['#max_delta']; $i++) {
      $form['exnid']['widget'][$i]['value']['#type'] = 'textfield';
      $form['exnid']['widget'][$i]['value']['#autocomplete_route_name'] = 'samhsa_term_elevation.matcher';
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $query = $form_state->getValue('query');

    $normalized_keys = _samhsa_term_elevation_normalize_keys($query[0]['value']);
    $entity->set('query', $normalized_keys[0]);

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label SAHMSA Term Elevation.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label SAHMSA Term Elevation.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.samhsa_term_elevation.canonical', ['samhsa_term_elevation' => $entity->id()]);
  }

}
