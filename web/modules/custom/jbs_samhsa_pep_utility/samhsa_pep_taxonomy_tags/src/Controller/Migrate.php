<?php


namespace Drupal\samhsa_pep_taxonomy_tags\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Migrate extends ControllerBase {

  private $tags_to_terms = [];

  private $fields = [];

  private $terms_to_add = [];

  private $all_tags;

  private $isDrush;

  private $nl;

  private $isTest;

  private $cnt_error;

  private $cnt_warn;

  public function __construct($isDrush = FALSE, $isTest = FALSE)
  {
    $this->fields['issues_conditions_disorders']     = 'field_issues_conditions_and_diso';
    $this->fields['professional_research_topics']    = 'field_professional_and_research_';
    $this->fields['substances']                      = 'field_substances';
    $this->fields['treatment_prevention_recovery']   = 'field_treatment_prevention_and_r';
    $this->fields['location']                        = 'field_location';

    $this->terms_to_add['Naloxone']                  = 'treatment_prevention_recovery';
    $this->terms_to_add['Naltrexone']                = 'treatment_prevention_recovery';
    $this->terms_to_add['Opioid Use Disorder']       = 'issues_conditions_disorders';
    $this->terms_to_add['Stimulants']                = 'substances';

    $this->isDrush = $isDrush;
    $this->nl = ($isDrush ? "\n" : "<br />");
    $this->output = 'called from ' . ($this->isDrush ? 'Drush command' : 'Menu router') . $this->nl;
    $this->isTest = $isTest;
    $this->cnt_error = 0;
    $this->cnt_warn  = 0;
    $this->all_tags = $this->get_all_tags('tags1');

  }

  public function getStatus() {
    $this->output .= $this->nl . $this->nl . 'Execution complete';
    if ($this->isTest) {
      $this->output .= $this->nl . "TEST mode status";
    }
    $this->output .= $this->nl . $this->cnt_error . ' errors, ' . $this->cnt_warn . ' warnings';
    if ($this->isDrush) {
      return $this->output;
    }
    else {
      return [
        '#markup' => $this->output,
      ];
    }
  }

  /**
   * master method to call all steps in migration
   */
  public function migrate()
  {
    $nl = $this->nl;

    // count the number of products in the catalog
    $product_ids = \Drupal::entityQuery('commerce_product')->execute();
    $numProducts = count($product_ids);
    $this->output .= $nl . "$numProducts products found.";
    if ($numProducts < 100) {
      $this->output .= $nl . "***WARNING only $numProducts found, entire product catalog may not have been migrated";
      $this->cnt_warn++;
    }

    $product_id = array_shift($product_ids);
    /* @var Drupal\commerce_product\Entity\Product */
    $product = \Drupal\commerce_product\Entity\Product::load($product_id);
    $this->output .= $nl . "Testing for taxonomy fields using product ID $product_id '" . $product->title->getValue()[0]['value'] . ".";
    foreach ($this->fields as $vocabulary => $field_name) {
      // confirm taxonomy entity reference fields are defined on the Product entity
      if (isset($product->$field_name)) {
        $this->output .= $nl . 'taxonomy field: ' . $field_name;
      }
      else {
        $this->output .= $nl . '***ERROR taxonomy field missing from Product: ' . $field_name;
        $this->cnt_error++;
      }
      // confirm all taxonomy Vocabularies are in place
      if (count($this->get_vocabulary($vocabulary))) {
        $this->output .= $nl . 'taxonomy vocabulary: ' . $vocabulary;
      }
      else {
        $this->output .= $nl . '***ERROR taxonomy vocabulary missing: ' . $vocabulary;
        $this->cnt_error++;
      }
    }

    $this->output .= $nl . count($this->all_tags) . " tags found in 'tags1' vocabulary";
    if (!count($this->all_tags)) {
      $this->output .= $nl . "***ERROR: no tag data found in 'tags1' vocabulary";
      $this->cnt_error++;
    }

    if (!$this->cnt_error) {
      $this->output .= $nl . "No errors found so continuing with migration";
      $this->newTerms();
      $this->assignTerms();
    }
    else {
      $this->output .= $nl . "errors were found, migration halted.  Review log messages.";
    }

    return $this->getStatus();
  }


  public function newTerms() {
    $nl = $this->nl;
    $this->output .= $nl . "Adding addtional Taxonomy terms";
    foreach ($this->terms_to_add as $name => $vocabulary) {
      // check if term already exists (in case this has been run)
      if (!$this->termExists($vocabulary, $name)) {
        if (!$this->isTest) {
          $this->addTerm($vocabulary, $name);
        }
        if ($this->termExists($vocabulary, $name)) {
          $this->output .= $nl . "term '$name' added to '$vocabulary'";
        }
        else {
          $this->output .= $nl . "***ERROR: term '$name' NOT added to '$vocabulary'";
          $this->cnt_error++;
        }
      }
      else {
        $this->output .= $nl . "$term '$name' already exists in vocabulary '$vocabulary'";
      }
    }
    return $this->getStatus();
  }


  public function assignTerms() {
    $nl = $this->nl;
    $product_ids = \Drupal::entityQuery('commerce_product')->execute();
    $tags_missing_terms = [];
    $all_products = [];
    $products_modified = [];
    $product_count = 0;
    $this->matchTags();

    foreach ($product_ids as $product_id) {
      /* @var Drupal\commerce_product\Entity\Product */
      $product = \Drupal\commerce_product\Entity\Product::load($product_id);
      $product_modified = FALSE;
      $product_title = $product->title->getValue()[0]['value'];
      $all_products[$product_id] = $product_title;
      $this->output .= $nl.$nl . $product_title . ' (' . $product_id . ')';

      // get all assigned terms from 'Tags2' vocabulary assigned to this Product
      $field_tags = $product->field_tags;
      $tag_tids = [];
      foreach ($field_tags as $delta => $value) {
        $tag_tids[] = $value->getValue()['target_id'];
      }
      $unmached_tids = $tag_tids;
      if (count($tag_tids)) {
        $this->output .= ' tags => ' . implode(',', $tag_tids);
        foreach ($this->tags_to_terms as $vocab_name => $vocab_terms) {
          // check for matching terms by vocabulary
          $field_name = $this->fields[$vocab_name];   // field_professional_and_research
          $term_field = $product->$field_name;        // $product->field_professional_and_research_
          $tids = [];
          foreach ($term_field as $delta => $value ) {
            // get any currently assigned terms for this vocabulary
            $tids[] = $value->getValue()['target_id'];
          }
          $before = implode(',',$tids);

          // look for corresponding Tags for each term in this vocabulary
          $cnt_tags_added = 0;
          foreach ($tag_tids as $tag_tid) {
            if (array_key_exists($tag_tid,$vocab_terms)) {
              // we have a match for this vocabulary
              $term_tid = $vocab_terms[$tag_tid];
              if (!in_array($term_tid, $tids)) {
                // add vocab term
                $tids[] = $tag_tid;
                $cnt_tags_added++;
              }
              unset($unmached_tids[array_search($tag_tid,$unmached_tids)]);
            }
          }
          // update the Product field with both existing terms and mapped Tags
          if ($cnt_tags_added) {
            $product->set($field_name, $tids);
            $product_modified = TRUE;
            $tids_after = [];
            foreach ($term_field as $delta => $value ) {
              $tids_after[] = $value->getValue()['target_id'];
            }
            $this->output .= $nl . " - ($vocab_name) " . $before . ' => ' . implode(',',$tids_after);
          }
        }
      }
      else {
        $this->output .= ' - no tags assigned';
      }

      if (count($unmached_tids)) {
        $missing = [];
        foreach ($unmached_tids as $tag_tid) {
          if (!in_array($tag_tid,$missing)) {
            $missing[] = $tag_tid;
            $tags_missing_terms[$tag_tid][] = $product_id;
          }
        }
        $this->output .= $nl . " - unmatched terms: " . implode(',',$missing);
      }
      else if (count($tag_tids)) {
        $this->output .= $nl . " - ALL terms matched";
      }

      if ($product_modified) {
        if (!$this->isTest) {
          $product->save();
        }
        $products_modified[$product_id] = $product_title;
        $this->output .= "...product saved";
      } else {
        $this->output .= "...no changes to product, not saving";
      }
      if (++$product_count > 10 && $this->isTest) {
        $this->output .= $nl . "TEST MODE - stopping after 10 products";
        break;
      }
    } // end Product

    $this->output .= $nl . $nl;
    $this->output .= $nl . count($products_modified) . " updated products";
    foreach ($products_modified as $pid => $title) {
      $this->output .= $nl . " - " . $title . " ($pid)";
    }

    $this->output .= $nl . $nl;
    foreach ($tags_missing_terms as $tid => $tag_missing) {
      $this->output .= $nl . "Tag without matching Term - " . $this->all_tags[$tid] . " ($tid)";
      foreach( $tag_missing as $pid) {
        $this->output .= $nl . " - " . $all_products[$pid] . " ($pid)";
      }
      $this->output .= $nl;
    }
    return  $this->output;
  }

  /**
   * matchTags
   *
   * match terms in "tags1" vocabulary to same terms in other vocabularies
   * @return array
   */
  public function matchTags() {
    $vocabularies   = $this->get_all_vocabularies();
    $product_terms  = [];
    $rows_duplicate = [];
    foreach ($vocabularies as $vid => $vocabulary_name) {
      switch ($vid) {
        case 'audience' :
        case 'format' :
        case 'issues_conditions_disorders' :
        case 'languages' :
        case 'location' :
        case 'population_group' :
        case 'professional_research_topics' :
        case 'publication_category' :
        case 'series' :
        case 'substances' :
        case 'treatment_prevention_recovery' :;
          foreach ($this->get_vocabulary($vid) as $name => $term) {
            $product_term = new \stdClass();
            $product_term->tid  = $term->tid;
            $product_term->vid  = $term->vid;
            $product_term->name = $term->name;
            if (!array_key_exists($name, $product_terms)) {
              $product_terms[$name] = $product_term;
            }
            else {
              $duplicates[$name] = $product_term;
              $rows_duplicate[] = [
                'tag'   => $name,
                'vid'   => $product_terms[$name]->vid,
                'tid'   => $product_terms[$name]->tid,
                'vid2'  => $vid,
                'tid2'  => $term->tid,
              ];
            }
          }
          break;

          default:
          // skip vocabulary
      }
    }

    $rows = [];
    $rows_missing = [];
    $rows_exception = [];
    foreach ($this->all_tags as $tid => $name) {
      if (strpos($name, '&amp;')) {
        $name_corrected = str_replace('&amp;', '&', $name);
        $rows_exception[] = [
          'tag'       => $name,
          'tag_tid'   => $tid,
          'corrected' => $name_corrected,
        ];
        $name = $name_corrected;
      }
      if ($name == 'Home & Community-Based Services') {
        $name_corrected = 'Home and Community-Based Services';
        $rows_exception[] = [
          'tag'       => $name,
          'tag_tid'   => $tid,
          'corrected' => $name_corrected,
        ];
        $name = $name_corrected;
      }
      if (array_key_exists($name, $product_terms)) {
        $tags_to_terms[$product_terms[$name]->vid][$tid] = $product_terms[$name]->tid;
        $rows[$name] = [
          'tag'       => $name,
          'tag_tid'   => $tid,
          'vocab'     => $vocabularies[$product_terms[$name]->vid],
          'vid'       => $product_terms[$name]->vid,
          'term'      => $product_terms[$name]->name,
          'term_tid'  => $product_terms[$name]->tid,
        ];
      }
      else {
        $rows_missing[$name] = [
          'tag'     => $name,
          'status'  => 'not found',
        ];
      }
    }

    foreach ($tags_to_terms as $vocab_name => $vocab_terms) {
      ksort($vocab_terms);
      $tags_to_terms[$vocab_name] = $vocab_terms;
    }
    $this->tags_to_terms = $tags_to_terms;

    $found = [
      '#type'   => 'table',
      '#weight' => 201,
      '#caption' => t('Tags matched to terms in a single Vocabulary'),
      '#header' => [
        'tag'       => t('Tag'),
        'tag_tid'   => t('Tag tid'),
        'vocab'     => t('Vocabulary'),
        'vid'       => t('vid'),
        'term'      => t('Term'),
        'term_tid'  => t('Term tid'),
      ],
      '#rows' => $rows,
    ];

    $missing = [
      '#type'   => 'table',
      '#caption' => t('Tags not found in any Vocabulary'),
      '#weight' => 201,
      '#header' => [
        'tag'    => t('Tag'),
        'status' => t('Status'),
      ],
      '#rows' => $rows_missing,
    ];

    $exception = [
      '#type'   => 'table',
      '#caption' => t('Modifications or exceptions'),
      '#weight' => 201,
      '#header' => [
        'tag'     => t('Tag'),
        'tag_tid' => t('Tag tid'),
        'status'  => t('Converted to'),
      ],
      '#rows' => $rows_exception,
    ];

    $duplicates = [
      '#type'   => 'table',
      '#caption' => t('Terms found in multiple Vocabularies'),
      '#weight' => 201,
      '#header' => [
        'tag'   => t('Tag'),
        'vocab' => t('Vocabulary'),
        'tid'   => t('Tag tid'),
        'vocab2'=> t('Second Vocabulary'),
        'tid2'  => t('Second Term tid'),
      ],
      '#rows' => $rows_duplicate,
    ];

    return ['tables' =>
      [
        'log' => [
          '#markup' => 'Success.<pre>' . print_r($this->tags_to_terms,true) . '</pre>',
        ],
        'found'       => $found,
        'missing'     => $missing,
        'exception'   => $exception,
        'duplicates'  => $duplicates,
      ]
    ];
  }

  private function get_all_vocabularies() {
    $vocabularies = [];
    foreach (\Drupal\taxonomy\Entity\Vocabulary::loadMultiple() as $vocab) {
      $name = $vocab->get('name');
      $vid  = $vocab->get('vid');
      $vocabularies[$vid] = $name;
    }
    return $vocabularies;
  }

  private function get_vocabulary($vid) {
    $terms = [];
    foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid) as $term) {
      $tag = new \stdClass();
      $tag->tid = $term->tid;
      $tag->vid = $term->vid;
      $tag->name = $term->name;
      $terms[$tag->name] = $tag;
    }
    return $terms;
  }

  private function get_all_tags($vid) {
    $tags = [];
    foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid) as $tag) {
      $tags[$tag->tid] = $tag->name;
    }
    return $tags;
  }
  private function termExists($vocabulary = '', $name = '') {
    if ($vocabulary == '' || $name == '') {
      return FALSE;
    }
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vocabulary);
    $query->condition('name', $name);
    $tid = $query->execute();
    return count($tid);
  }

  private function addTerm($vocabulary = '', $name = '') {
    if ($vocabulary == '' || $name == '') {
      return FALSE;
    }
    $term = \Drupal\taxonomy\Entity\Term::create([
      'vid'  => $vocabulary,
      'name' => $name,
    ]);
    $term->save();
  }
}



