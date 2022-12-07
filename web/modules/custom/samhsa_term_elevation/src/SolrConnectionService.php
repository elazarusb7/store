<?php

namespace Drupal\samhsa_term_elevation;

use Drupal;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\SearchApiException;
use Drupal\search_api_solr\SearchApiSolrException;
use Exception;

/**
 * Class SolrConnectionService.
 */
class SolrConnectionService {

  private $titleFieldName = '';

  private $solrCoreId = '';

  private $solrIndexId = '';

  /**
   * Constructs a new SolrConnectionService object.
   */
  public function __construct() {
    $config = Drupal::config('samhsa_term_elevation.config');
    $this->solrCoreId = $config->get('server_id');
    $this->solrIndexId = $config->get('index_id');
    $this->titleFieldName = $config->get('title_id');
  }

  /**
   * Queries Solr to search for occurrences of a string in the Title field.
   *
   * @param $string
   *   String to be searched for.
   * @param int $limit
   *   Limit of results returned.
   *
   * @return array
   *   List of Titles where the string occurs.
   */
  public function searchString($string, $limit = 10) {
    $results = [];
    if (Index::load($this->solrIndexId)->isServerEnabled()) {
      $query = Index::load($this->solrIndexId)->query();
      $term = $this->titleFieldName . ':' . trim($string) . '*';
      $query->keys($term);
      $parse_mode_manager = Drupal::service('plugin.manager.search_api.parse_mode');
      $query->setParseMode($parse_mode_manager->createInstance('direct'));
      $data = $query->execute();
      foreach ($data->getResultItems() as $item_id => $item) {
        $item_fields = $item->getExtraData('search_api_solr_document')
          ->getFields();
        $title = $item_fields[$this->titleFieldName][0];
        if (preg_match('/https:/', $item_fields['site'])) {
          $site = substr($item_fields['site'], 8);
        }
        elseif (preg_match('/http:/', $item_fields['site'])) {
          $site = substr($item_fields['site'], 7);
        }
        else {
          $site = $item_fields['site'];
        }
        if ($site[strlen($site) - 1] == '/') {
          $site = substr($site, 0, -1);
        }
        $results[] = [
          'value' => $title . ' (' . $item_fields['id'] . ')',
          'label' => $title . ' (' . $site . ')',
        ];
      }
    }
    return $results;
  }

  /**
   * Queries Solr to extract the different websites bases.
   *
   * Sample request: select?fl=hash,%20site&group.field=site&group=true&q=*:*
   *
   * @return array
   *   List of websites bases indexed by their hash.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getWebsitesBases() {
    $result = [];
    $servers = [];
    foreach (search_api_solr_get_servers() as $server) {
      $servers[$server->id()] = $server;
    }
    if (!isset($servers[$this->solrCoreId])) {
      return $result;
    }
    $connector = $servers[$this->solrCoreId]->getBackend()->getSolrConnector();
    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('*:*');
    $solarium_query->removeField('*');
    $solarium_query->removeField('score');
    $solarium_query->addField('hash');
    $solarium_query->addParam('group', TRUE);
    $solarium_query->addParam('group.field', 'site');
    $response = $connector->search($solarium_query);
    $data = json_decode($response->getBody());
    foreach ($data->grouped->site->groups as $group) {
      $result[$group->doclist->docs[0]->hash] = $group->groupValue;
    }
    return $result;
  }

  /**
   * Queries Solr to extract the names off all indexed fields.
   *
   * Sample request: select?q=*:*&wt=csv&rows=0&facet.
   *
   * @param string $core_id
   *   Id of the Solr core.
   *
   * @return array
   *   List of the names off all indexed fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getIndexedFieldNames($core_id) {
    $result = [];
    $servers = [];
    foreach (search_api_solr_get_servers() as $server) {
      $servers[$server->id()] = $server;
    }
    if (!$connector = $servers[$core_id]->getBackend()->getSolrConnector()) {
      return $result;
    }
    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('*:*');
    $solarium_query->addParam('rows', 0);
    $solarium_query->addParam('wt', 'csv');
    $solarium_query->addParam('facet', TRUE);
    try {
      $response = $connector->search($solarium_query);
      $result = explode(',', $response->getBody());
    } catch (SearchApiSolrException $e) {
      Drupal::messenger()
        ->addMessage(t("Solr environment couldn't be reached. Please, check Search Api configurations"), 'error');
      $result = FALSE;
    } catch (Exception $e) {
      $result = FALSE;
    }
    return $result;
  }

  /**
   * Queries Solr to extract all possible values for base URL.
   *
   * Sample request:
   * select?facet.field=site&facet=on&fl=site&q=*:*&rows=0&wt=json.
   *
   * @param string $core_id
   *   Id of the Solr core.
   *
   * @return array
   *   List of the names off all indexed fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getMultiSiteComponents($core_id) {
    $result = [];
    $servers = [];
    try {
      foreach (search_api_solr_get_servers() as $server) {
        $servers[$server->id()] = $server;
      }
      if (!$servers || !$connector = $servers[$core_id]->getBackend()
          ->getSolrConnector()) {
        return $result;
      }
      $solarium_query = $connector->getSelectQuery();
      $solarium_query->setQuery('*:*');
      $solarium_query->addParam('fl', 'site');
      $solarium_query->addParam('rows', 0);
      $solarium_query->addParam('wt', 'json');
      $solarium_query->addParam('facet', TRUE);
      $solarium_query->addParam('facet.field', 'site');
      $response = $connector->search($solarium_query);
      $body = json_decode($response->getBody());
      foreach (@$body->facet_counts->facet_fields->site as $item) {
        if (!is_int($item)) {
          $result[] = $item;
        }
      }
    } catch (SearchApiException $e) {
      Drupal::messenger()
        ->addError(t('Invalid server configuration for Solr: %core_id', ['%core_id' => $core_id]));
    } catch (Exception $e) {
      Drupal::messenger()
        ->addError(t('Invalid server configuration for Solr.'));
    }
    return $result;
  }

}
