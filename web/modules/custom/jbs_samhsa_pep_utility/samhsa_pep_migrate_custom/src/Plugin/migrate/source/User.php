<?php

namespace Drupal\samhsa_pep_migrate_custom\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "custom_user_d7"
 * )
 */
class User extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 1, '>');
    // Vera d7 user id =10, Joseph d7 user id = 1. Do Not Import.
    // ->condition('uid', array(1, 10), 'NOT IN');.
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['field_first_name'] = $this->t('First Name');
    $fields['field_last_name'] = $this->t('Last Name');
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $uid = $row->getSourceProperty('uid');
    $bundle = "user";
    // field_first_name.
    $query = '
    SELECT field_first_name_value
    FROM field_data_field_first_name
    WHERE entity_id = :uid AND bundle = :bundle';

    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->field_first_name_value;
    }
    $row->setSourceProperty('field_first_name', $values);

    // field_last_name.
    $query = '
    SELECT field_last_name_value
    FROM field_data_field_last_name
    WHERE entity_id = :uid AND bundle = :bundle';

    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->field_last_name_value;
    }
    $row->setSourceProperty('field_last_name', $values);

    // field_organization.
    $query = '
    SELECT field_organization_value
    FROM field_data_field_organization
    WHERE entity_id = :uid AND bundle = :bundle';

    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->field_organization_value;
    }
    $row->setSourceProperty('field_organization', $values);

    // field_phone_number.
    $query = '
    SELECT field_phone_number_value
    FROM field_data_field_phone_number
    WHERE entity_id = :uid AND bundle = :bundle';

    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->field_phone_number_value;
    }
    $row->setSourceProperty('field_phone_number', $values);

    // field_rejected_user.
    $query = '
    SELECT field_rejected_user_value
    FROM field_data_field_rejected_user
    WHERE entity_id = :uid AND bundle = :bundle';

    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->field_rejected_user_value;
    }
    $row->setSourceProperty('field_rejected_user', $values);

    // field_country_hidden.
    $query = '
    SELECT field_country_hidden_value
    FROM field_data_field_country_hidden
    WHERE entity_id = :uid AND bundle = :bundle';

    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->field_country_hidden_value;
    }
    $row->setSourceProperty('field_country_hidden', $values);

    // location.
    $result = $this->getDatabase()->query('select fl.field_location_lid,
      l.city,
      l.country,
      l.postal_code,
      l.province,
      l.street,
      l.additional
    FROM field_data_field_location fl
    INNER JOIN location l
    ON fl.field_location_lid = l.lid
    WHERE
      fl.entity_id = :uid AND bundle = :bundle
  ', [':uid' => $uid, ':bundle' => $bundle]);
    foreach ($result as $record) {
      $row->setSourceProperty('city', $record->city);
      $row->setSourceProperty('country', $record->country);
      $row->setSourceProperty('postal_code', $record->postal_code);
      $row->setSourceProperty('province', $record->province);
      $row->setSourceProperty('street', $record->street);
      $row->setSourceProperty('additional', $record->additional);

      // Set address field fields.
      $row->setSourceProperty('address_city', $record->city);
      $row->setSourceProperty('address_country', strtoupper($record->country));
      $row->setSourceProperty('address_postal_code', $record->postal_code);
      $row->setSourceProperty('address_province', strtoupper($record->province));
      $row->setSourceProperty('address_street', $record->street);
      $row->setSourceProperty('address_additional', $record->additional);

    }

    // field_date_approved.
    $query = 'SELECT field_date_approved_value
      FROM
      field_data_field_date_approved
      WHERE entity_id = :uid AND bundle = :bundle';
    $result = $this->getDatabase()->query($query, [
      ':uid' => $uid,
      ':bundle' => $bundle,
    ]);
    $values = [];
    foreach ($result as $record) {
      $values[] = date('Y-m-d', strtotime($record->field_date_approved_value));
    }
    $row->setSourceProperty('field_date_approved', $values);

    // user_role.
    $query = $this->select('users_roles', 'r');
    $query->fields('r', ['rid']);
    $query->condition('r.uid', $uid, '=');
    $record = $query->execute()->fetchAllKeyed();
    $row->setSourceProperty('roles', array_keys($record));

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }

  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = [
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'signature' => $this->t('Signature'),
      'signature_format' => $this->t('Signature format'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'status' => $this->t('Status'),
      'timezone' => $this->t('Timezone'),
      'language' => $this->t('Language'),
      'picture' => $this->t('Picture'),
      'init' => $this->t('Init'),
    ];
    return $fields;

  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }

}
