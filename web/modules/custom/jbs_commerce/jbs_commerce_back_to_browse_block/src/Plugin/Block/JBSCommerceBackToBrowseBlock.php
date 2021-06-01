<?php

namespace Drupal\jbs_commerce_back_to_browse_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Url;


/**
 * Creates a custom block for showing a link back to the store homepage and a link to return to search results
 *
 * @Block(
 *   id = "jbs_commerce_back_to_browse_block",
 *   admin_label = @Translation("Store Navigation")
 * )
 */
class JBSCommerceBackToBrowseBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $front_alias =  \Drupal\Core\Url::fromRoute('<front>')->toString();
    $base_path = \Drupal::request()->getSchemeAndHttpHost();                     // ex. http://pep-b.pep
    $check_referer = "";
    if(isset(\Drupal::request()->query)) {
      $check_referer = \Drupal::request()->query->get('referer');
    }

    // ex. http://pep-b.pep/?search_api_fulltext=meth&sort_bef_combine=search_api_relevance%20DESC
    $referer = \Drupal::request()->server->get('HTTP_REFERER');
    /*if (
        $referer == $base_path ||
        strpos($referer, $base_path . "/?f[0]=") !== FALSE ||
        $check_referer == "from_search_result") {
        $results_link = '<li class="menu-item"><a href="' . $referer . '">Return to search&nbsp;results</a></li>';
    } else {
        $results_link = '';
    }*/

    /*BK 3/2/20 - not sure why we need to include the base path?
    if (strpos($referer, $base_path . "/?f[0]=") !== FALSE ||
        strpos($referer, $base_path . "/?search_api_fulltext=") !== FALSE ||
        strpos($referer, $base_path . "?f%5B0%5D") !== FALSE ) {
    */
    if (strpos($referer, "f[0]=")                 !== FALSE ||  // faceted search
        strpos($referer, "search_api_fulltext=")  !== FALSE ||  // search text
        strpos($referer, "f%5B0%5D")              !== FALSE )   // ???
    {
      $results_link = '
    <li class="menu-item return-btn">
      <a href="' . $referer . '">Return to Search Results</a>
    </li>';
    } else {
      $results_link = '';
    }

    return array(
      '#cache' => array(
        'contexts' => array('url.path'),
        'max-age' => 0,
      ),
      '#markup' => '
<!-- BEGIN jbs_commerce_back_to_browse_block -->
<nav class="text-menu" aria-labelledby="searchnav">
  <ul class="menu">' . $results_link . '
    <li class="menu-item new-search-btn">
      <a href="' . $front_alias . '">Start a New Search</a>
    </li>
  </ul>
</nav>
<!-- END jbs_commerce_back_to_browse_block -->',
    );
  }

}
