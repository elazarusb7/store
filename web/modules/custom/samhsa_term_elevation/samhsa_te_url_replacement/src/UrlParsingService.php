<?php

namespace Drupal\samhsa_te_url_replacement;

/**
 * Class UrlParsinService.
 */
class UrlParsingService {

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
   * @throws \Drupal\search_api\SearchApiException
   */
  public function getSites($core_id) {
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
    return $result;
  }

}
