<?php

namespace Drupal\samhsa_pep_taxonomy_tags\Controller;

use Drupal;
use Drupal\taxonomy\Entity\Term;
use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class TaxonomyUpdate extends ControllerBase {

  private $isDrush;

  private $fields = [];

  private $nl;

  private $isTest;

  private $cnt_error;

  private $cnt_warn;

  private $output;

  private $taxonomy_terms;

  private $taxonomy_map;

  private $vocabularies;

  /**
   *
   */
  public function __construct($isDrush = FALSE, $isTest = FALSE) {
    $this->isDrush = $isDrush;
    $this->nl = ($isDrush ? "\n" : "<br />");
    $this->output .= $this->nl . 'called from ' . ($this->isDrush ? 'Drush command' : 'Menu router') . $this->nl;
    $this->isTest = $isTest;
    $this->output = '';
    $this->fields['publication_category'] = 'field_publication_category';
    $this->fields['publication_target_audience'] = 'field_pub_target_audience';

    $this->terms_to_add['Mental Health'] = 'publication_category';
    $this->terms_to_add['Substance Abuse'] = 'publication_category';
    $this->terms_to_add['Practitioner/Professional'] = 'publication_target_audience';
    $this->terms_to_add['General Public'] = 'publication_target_audience';

    $this->taxonomy_terms = $this->getProductTaxonomies();
    $this->taxonomy_map = $this->getTaxonomyMap();
    foreach ($this->taxonomy_terms as $vocabulary => $field) {
      $this->vocabularies[$vocabulary] = $this->get_vocabulary($vocabulary);
    }

    $this->cnt_error = 0;
    $this->cnt_warn = 0;
    $this->output .= $this->nl . "TaxonomyUpdate active";
  }

  /**
   *
   */
  public function update() {
    $nl = $this->nl;
    $this->output .= $nl . $nl . 'Begin execution';
    $module_handler = Drupal::service('module_handler');
    $module_path = $module_handler->getModule('samhsa_pep_taxonomy_tags')
      ->getPath();
    $this->output .= $nl . "module path: $module_path";
    $filename = "/publication_taxonomy.csv";
    $records = 0;
    $cnt_product = 0;

    // Check for new terms.
    $new_terms = [
      'Substance Abuse' => 'publication_category',
      'Mental Health' => 'publication_category',
      'Practitioner/Professional' => 'publication_target_audience',
      'General Public' => 'publication_target_audience',
    ];

    $missing_terms = 0;
    foreach ($new_terms as $term => $vocab) {
      $this->output .= $nl . "verifing '$term' in '$vocab'";
      try {
        if (isset($this->vocabularies[$vocab][$term])) {
          $tids[$term] = $this->vocabularies[$vocab][$term];
          $this->output .= $nl . "verified '$term' as tid " . print_r($tids[$term]);
        }
        else {
          $missing_terms++;
          $this->output .= $nl . "***ERROR term '$term' not found in $vocab vocabulary.";
          $t2 = $this->vocabularies[$vocab][$term];
          $this->output .= $nl . ($term == $t2 ? 'match' : 'no match') . " '$term' != '$t2'";
          $this->output .= $nl . print_r($this->vocabularies[$vocab], TRUE);
          foreach ($this->vocabularies[$vocab] as $i => $t) {
            // $this->output .= $nl . "$vocab: " . print_r($t,true);
            // $this->output .= $nl . "$vocab: [$i]" . $t->name;
          }
        }
      } catch (Exception $e) {
        $this->output .= $nl . "***ERROR " . print_r($e, TRUE);
        return $this->output;
      }
    }

    if ($missing_terms) {
      $this->output .= $nl . "$missing_terms new term(s) missing...exiting";
      $this->cnt_error += $missing_terms;
      return $this->output;
    }

    // Read in data from .csv.
    $data = [];
    $not_found_skus = [];
    try {
      $handle = fopen($module_path . $filename, "r");
    } catch (Exception $e) {
      $this->output .= $nl . "ERROR: $e";
      return $this->output;
    }

    if ($handle) {
      $this->output .= $this->nl . "input file open: '$module_path$filename'";
      while (!feof($handle)) {
        $row = fgetcsv($handle);
        if (is_array($row)) {
          $data[] = $row;
          $records++;
        }
      }
      fclose($handle);
    }
    else {
      $this->output .= $nl . "unable to open file: $module_path$filename";
      return $this->output;
    }

    // All data read in.
    $this->output .= $this->nl . "successfully read $records records";
    $entity_manager = Drupal::entityTypeManager();

    // Get header.
    /*[
    [0] => Govt Pub Number
    [1] => Substance Abuse
    [2] => Mental Health
    [3] => Practitioner/Professional
    [4] => General Public
    ]*/
    $header = array_shift($data);
    foreach ($data as $d) {
      $sku = $d[0];
      if (!isset($sku) || $sku == '') {
        $this->output .= $nl . 'invalid SKU: ' . print_r($d, TRUE);
        continue;
      }

      $query = Drupal::entityQuery('commerce_product_variation')
        ->condition('sku', $sku);
      $variation_id = array_shift($query->execute());
      if (!isset($variation_id) || $variation_id == '') {
        $this->output .= $nl . "***cannot locate variant for SKU '$sku'";
        $not_found_skus[] = $sku;
        $this->cnt_error++;
        continue;
      }

      $product_variation = $entity_manager->getStorage('commerce_product_variation')
        ->load($variation_id);
      if (!isset($product_variation)) {
        $this->output .= $nl . "***cannot load product variation for SKU '$sku'";
        $this->cnt_error++;
        continue;
      }

      $product_id = $product_variation->get('product_id')->first();
      if (!isset($product_id) || $product_id == '') {
        $this->output .= $nl . "***product_id not found on variant $variation_id";
        $this->cnt_error++;
        continue;
      }

      $product_id = $product_id->getValue('target_id')['target_id'];
      if (!isset($product_id) || $product_id == '') {
        $this->output .= $nl . "***target_id not found on variant $variation_id";
        $this->cnt_error++;
        continue;
      }

      $product = Product::load($product_id);
      if (!isset($product)) {
        $this->output .= $nl . "***product_id '$product_id' could not be loaded";
        $this->cnt_error++;
        continue;
      }

      // let's go.
      $msg = $d[0] . "\tpid: " . $product_id . "\tvid: " . $variation_id;
      try {
        if ($d[1] == 'X') {
          // Substance Abuse (publication_category)
          $this->assignTerm($tids['Substance Abuse'], $this->fields['publication_category'], $product);
          $msg .= "\t" . $tids['Substance Abuse'];
        }
        if ($d[2] == 'X') {
          // Mental Health (publication_category)
          $this->assignTerm($tids['Mental Health'], $this->fields['publication_category'], $product);
          $msg .= "\t" . $tids['Mental Health'];
        }
        if ($d[3] == 'X') {
          // Practitioner/Professional (publication_target_audience)
          $this->assignTerm($tids['Practitioner/Professional'], $this->fields['publication_target_audience'], $product);
          $msg .= "\t" . $tids['Practitioner/Professional'];
        }
        if ($d[4] == 'X') {
          // General Public (publication_target_audience)
          $this->assignTerm($tids['General Public'], $this->fields['publication_target_audience'], $product);
          $msg .= "\t" . $tids['General Public'];
        }
      } catch (Exception $e) {
        $this->output .= $nl . "ERROR: $e";
        return $this->output;
      }
      if ($msg != '') {
        $this->output .= $nl . $msg;
      }

      $product->save();
      $cnt_product++;
      // If ($cnt_product > 1) break;.
    }

    $this->output .= $nl . $nl . 'missing SKU\'s: ' . print_r($not_found_skus, TRUE);
    $this->output .= $this->nl . "$cnt_product products updated";

    return $this->output;
  }

  /**
   *
   */
  public function getStatus() {
    $this->output .= $this->nl . $this->nl . 'Execution complete';
    if ($this->isTest) {
      $this->output .= $this->nl . "TEST mode status";
    }
    $this->output .= $this->nl . $this->cnt_error . ' errors, ' . $this->cnt_warn . ' warnings';
    return $this->output;
  }

  /**
   *
   */
  private function assignTerm($tid, $field_name, $product) {
    $tids = [];
    foreach ($product->$field_name as $delta => $value) {
      $tids[] = $value->getValue()['target_id'];
    }
    $tids[] = $tid;
    $product->set($field_name, $tids);
  }

  /**
   *
   */
  private function termExists($vocabulary = '', $name = '') {
    if ($vocabulary == '' || $name == '') {
      return FALSE;
    }
    $query = Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vocabulary);
    $query->condition('name', $name);
    $tid = $query->execute();
    return count($tid);
  }

  /**
   *
   */
  private function addTerm($vocabulary = '', $name = '') {
    if ($vocabulary == '' || $name == '') {
      return FALSE;
    }
    $term = Term::create([
      'vid' => $vocabulary,
      'name' => $name,
    ]);
    $term->save();
  }

  /**
   *
   */
  private function get_vocabulary($vid) {
    $terms = [];
    $storage = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid);
    foreach ($storage as $term) {
      $terms[$term->name] = $term->tid;
    }
    return $terms;
  }

  /**
   *
   */
  private function getProductTaxonomies() {
    $bundle_fields = Drupal::service('entity_field.manager')
      ->getFieldDefinitions('commerce_product', 'samhsa_publication');
    foreach (array_keys($bundle_fields) as $field_name) {
      $field = $bundle_fields[$field_name];
      if ($field->getType() == 'entity_reference') {
        $name = 'commerce_product.samhsa_publication.' . $field_name;
        $field_config = Drupal::entityTypeManager()
          ->getStorage('field_config')
          ->load($name);
        if (isset($field_config)) {
          $settings = $field_config->getSettings();
          // If ($field_name == 'field_treatment_prevention_and_r') $this->output .= $this->nl . __LINE__ . print_r($settings,true);.
          if (isset($settings['target_type']) && $settings['target_type'] == 'taxonomy_term') {
            if (isset($settings['handler_settings']['target_bundles']) && count($settings['handler_settings']['target_bundles'])) {
              // $this->output .= $this->nl . __LINE__ . ' target_bundles:' . print_r($settings['handler_settings']['target_bundles'],true);
              $vocab = array_shift($settings['handler_settings']['target_bundles']);
              $vocabs[$vocab] = $field_name;
              $this->output .= $this->nl . "[$field_name] vocabulary: $vocab";
            }
            else {
              $this->output .= $this->nl . __LINE__ . " No target_bundles for $name";
            }
          }
        }
      }
    }
    $this->output .= $this->nl . "getProductTaxonomies():" . print_r($vocabs, TRUE);
    return $vocabs;
  }

  /**
   *
   */
  private function getTaxonomyMap() {
    return [
      // Vocabulary                         Product Field.
      'audience' => 'field_audience',
      'format' => 'field_format',
      'issues_conditions_disorders' => 'field_issues_conditions_and_diso',
      'location' => 'field_location',
      'population_group' => 'field_population_group',
      'professional_research_topics' => 'field_professional_and_research_',
      'publication_category' => 'field_publication_category',
      'publication_target_audience' => 'field_pub_target_audience',
      'series' => 'field_series',
      'substances' => 'field_substances',
      'tags1' => 'field_tags',
      'treatment_prevention_recovery' => 'field_treatment_prevention_and_r',
    ];
  }

  /**
   *
   */
  private function verifyTaxonomyFields() {
    if (!isset($this->taxonomy_terms) || !is_array($this->taxonomy_terms)) {
      $this->output .= $this->nl . "no taxonomy fields to verify";
      return FALSE;
    }

    $invalid = 0;
    foreach ($this->taxonomy_map as $vocab => $field) {
      if ($this->taxonomy_terms[$vocab] == $field) {
        $this->output .= $this->nl . "verified: $vocab -> product field: $field";
      }
      else {
        $invalid++;
      }
    }
    return $invalid;
  }

}
