<?php

/**
 * @file
 * Contains samhsa_term_elevation\samhsa_term_elevation.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */

/**
 * Implements hook_views_data().
 */
function samhsa_term_elevation_views_data() {

  $config = \Drupal::config('samhsa_term_elevation.config');
  $default_index_id = $config->get('index_id');
  $entity = 'search_api_index_' . $default_index_id;

  // Fields.
  $data[$entity]['title_multisite'] = [
    'title' => t('Title Multisite'),
    'help' => t('Provides a link to the source site'),
    'field' => [
      'title' => t('Title Multisite'),
      'help' => t('Provides a link to the source site'),
      'id' => 'title_multisite',
    ],
  ];

  $data[$entity]['content_url'] = [
    'title' => t("Content's URL"),
    'help' => t("Provides a URL to the content's page"),
    'field' => [
      'title' => t("Content's URL"),
      'help' => t("Provides a URL to the content's page"),
      'id' => 'content_url',
    ],
  ];

  $data[$entity]['website_base'] = [
    'title' => t('Website Base'),
    'help' => t('Base URL to component of multisite'),
    'field' => [
      'title' => t('Website Base'),
      'help' => t('Base URL to component of multisite'),
      'id' => 'website_base',
    ],
  ];

  $data[$entity]['highlighted_body'] = [
    'title' => t('Highlighted body'),
    'help' => t('Body text with highlighted words.'),
    'field' => [
      'title' => t('Highlighted body'),
      'help' => t('Body text with highlighted words.'),
      'id' => 'highlighted_body',
    ],
  ];

  $data[$entity]['document_date'] = [
    'title' => t('Document date'),
    'help' => t('Date of last update of the document.'),
    'field' => [
      'id' => 'document_date',
    ],
  ];

  $data[$entity]['description'] = [
    'title' => t('Description'),
    'help' => t("Description of the document's content."),
    'field' => [
      'id' => 'description',
    ],
  ];

  // Filters.
  $data[$entity]['website_base_filter'] = [
    'title' => t('Website base'),
    'filter' => [
      'title' => t('Website base'),
      'help' => t('Base URL for the websites that compound the multi-site environment.'),
      'field' => 'website_base',
      'id' => 'website_base_filter',
    ],
  ];

  return $data;
}
