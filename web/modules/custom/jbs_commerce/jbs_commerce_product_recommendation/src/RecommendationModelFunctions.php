<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_product_recommendation\RecommendationModelFunctions.
 */

namespace Drupal\jbs_commerce_product_recommendation;

use Drupal\commerce_cart\Event\CartEvents;

class RecommendationModelFunctions implements RecommendationModelFunctionsInterface {

  private $eventsTableName;
  private $pairsTableName;
  private $logTableName;
  private $eventNames = [
    CartEvents::CART_ENTITY_ADD => 'cart_entity_added',
    CartEvents::CART_ORDER_ITEM_REMOVE => 'cart_entity_removed',
    'commerce_order.place.post_transition' => 'order_placed',
  ];

  /**
   * RecommendationModelFunctions constructor.
   */
  public function __construct() {
    $this->eventsTableName = \Drupal::config('jbs_commerce_product_recommendation.settings')->get('eventsTableName');
    $this->pairsTableName = \Drupal::config('jbs_commerce_product_recommendation.settings')->get('pairsTableName');
    $this->logTableName = \Drupal::config('jbs_commerce_product_recommendation.settings')->get('logTableName');
  }

  /**
   * Recommendation Engine Functions
   */

  public function refreshModel() {
    $db = \Drupal::database();

    $lastTimestamp = \Drupal::config('jbs_commerce_product_recommendation.settings')->get('lastUpdate');

    if (!empty($lastTimestamp)) {
      $lastUpdate = $db->query('SELECT cutoff FROM {' . t($this->logTableName) . '} WHERE id=(SELECT MAX(id) FROM {' . t($this->logTableName) . '})');
      $lastTimestamp = $lastUpdate->fetchAll()[0]->cutoff ?? 0;
      //\Drupal::messenger()->addMessage('last = ' . $lastTimestamp);
    }

    // SIZE WARNING, get all session ids ordered by timestamp
    $query = $db->query('SELECT DISTINCT session_id FROM (SELECT DISTINCT session_id, timestamp FROM {'.t($this->eventsTableName).'} ORDER BY timestamp) AS sessions WHERE timestamp > :cutoff',
      [
        ':cutoff' => $lastTimestamp,
      ]);
    $sessions = $query->fetchAll();

    $matrix = array();
    $cutoff = 0;

    if (!empty($sessions)) {
      foreach ($sessions as $s => $session) {
        // TODO: add timestamp cutoff in query
        $events = $db->select($this->eventsTableName, 'events')
          ->fields('events', ['content_id', 'timestamp', 'event'])
          ->condition('session_id', $session->session_id, '=')
          ->execute()->fetchAll();

        //      $clickEvents = array_filter($events, function ($e) {
        //        if ($e->event === 'click') {
        //          return $e;
        //        }
        //      });
        //      $cartEvents = array_filter($events, function ($e) {
        //        if ($e->event === $this->eventNames[CartEvents::CART_ENTITY_ADD] || $e->event === $this->eventNames[CartEvents::CART_ORDER_ITEM_REMOVE]) {
        //          return $e;
        //        }
        //      });
        //      $orderEvents = array_filter($events, function ($e) {
        //        if ($e->event === $this->eventNames['commerce_order.place.post_transition']) {
        //          return $e;
        //        }
        //      });

        $this->mergeMatrices($matrix, $this->computeWeights($events));
        $cutoff = $events[count($events) - 1]->timestamp;
      }

      $this->updateScores($matrix, count($sessions));
      $this->updateLog($cutoff);
      if (\Drupal::service('router.admin_context')->isAdminRoute()) {
        \Drupal::messenger()->addMessage('The Recommendation Model has been updated.');
      }
    } else {
      if (\Drupal::service('router.admin_context')->isAdminRoute()) {
        \Drupal::messenger()->addMessage('No changes have been made to the Recommendation Model: No new data added since last refresh.');
      }
    }
  }

  public function updateScores($matrix, $numSessions) {
    $db = \Drupal::database();

    $minPrecision = 1e-9;

    $minScore = INF;
    $maxScore = 0;
    foreach ($matrix as $key => $values) {
      array_walk($matrix[$key], function (&$row) use ($numSessions) {
        $row /= (float)$numSessions;
      });
      if (min($matrix[$key]) < $minScore) {
        $minScore = min($matrix[$key]);
      }
      if (max($matrix[$key]) > $maxScore) {
        $maxScore = max($matrix[$key]);
      }
    }

    // Normalize scores
    foreach ($matrix as $key => $values) {
      array_walk($matrix[$key], function (&$row) use ($minScore, $maxScore) {
        $row = ($row - $minScore) / ($maxScore - $minScore);
      });
    }

    // SIZE WARNING, get all pairs
    $pairs = $db->select($this->pairsTableName, 'pairs')
      ->fields('pairs', ['p1', 'p2', 'score', 'count'])
      ->execute()->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_ASSOC);

    $products = array_keys($matrix);
    if (empty($pairs)) {
      $query = $db->insert($this->pairsTableName)->fields(['p1', 'p2' , 'score', 'count']);
      for ($i = 0; $i < count($products); $i++) {
        for ($j = ($i + 1); $j < count($matrix[$products[$i]]); $j++) {
          $p1 = min($products[$i], $products[$j]);
          $p2 = max($products[$i], $products[$j]);
          if ($matrix[$p1][$p2] > 0) {
            $query->values(
              array(
                'p1' => $p1,
                'p2' => $p2,
                'score' => max($matrix[$p1][$p2], $minPrecision),
                'count' => $numSessions,
              )
            );
          }
        }
      }
      $query->execute();
    } else {
      $insert = $db->insert($this->pairsTableName)->fields(['p1', 'p2', 'score', 'count']);

      // Compute the influence that the new data will have on the old data
      $influence = function ($new, $old, $counts) {
        return ($old + (($new - $old) * (1 / (float) $counts)));
      };

      foreach ($pairs as $p => $pair) {
        $pairs[$p] = array_combine(array_column($pairs[$p], 'p2'), $pairs[$p]);
      }
      for ($i = 0; $i < count($products); $i++) {
        for ($j = ($i + 1); $j < count($matrix[$products[$i]]); $j++) {
          $p1 = min($products[$i], $products[$j]);
          $p2 = max($products[$i], $products[$j]);
          if ($matrix[$p1][$p2] > 0) {
            if (!empty($pairs[$p1][$p2])) {
              $db->update($this->pairsTableName)->fields([
                'score' => max($influence($matrix[$p1][$p2], ($pairs[$p1][$p2]['score'] ? $pairs[$p1][$p2]['score'] : 0),
                  $numSessions + ($pairs[$p1][$p2]['count'] ? $pairs[$p1][$p2]['count'] : 0)), $minPrecision),
                'count' => $numSessions + ($pairs[$p1][$p2]['count'] ? $pairs[$p1][$p2]['count'] : 0),
              ])
                ->condition('p1', $p1, '=')
                ->condition('p2', $p2, '=')
                ->execute();
            } else {
              $insert->values([
                'p1' => $p1,
                'p2' => $p2,
                'score' => max($matrix[$p1][$p2], $minPrecision),
                'count' => $numSessions,
              ]);
            }
          }
        }
      }
      $insert->execute();
    }
  }

  public function computeWeights($events) {
    $path = array();
    $click = array();
    $cart = array();
    $order = array();

    foreach ($events as $e => $event) {
      if ($event->event !== $this->eventNames[CartEvents::CART_ORDER_ITEM_REMOVE]) {
        if ($event->event === 'click') {
          $click[] = $event->content_id;
        } else if ($event->event === $this->eventNames[CartEvents::CART_ENTITY_ADD]) {
          $cart[$event->content_id] = TRUE;
        } else if ($event->event === $this->eventNames['commerce_order.place.post_transition']) {
          $order[$event->content_id] = TRUE;
        }
        $path[] = $event->content_id;
      } else {
        $cart[$event->content_id] = FALSE;
      }
    }

    $totalEvents = count($path);
    $clickFreq = count($click) / (float) $totalEvents;
    $cartFreq = count($cart) / (float) $totalEvents;
    $orderFreq = count($order) / (float) $totalEvents;

    // Every additional step halves the strength of a relationship between products
    $strength = function ($steps) {
      return (float)0.5 ** ($steps - 1);
    };

    // Product pair matrix for the current session is initialized here
    $unique_events = array_unique($path);
    $matrix = array_fill_keys($unique_events, array_fill_keys($unique_events, 0));

    foreach ($unique_events as $u => $event) {
      $indices = array_keys($path, $event);

      for ($i = 0; $i < $totalEvents; $i++) {
        $shortestPathLength = array();
        if ($event == $path[$i]) {
          continue;
        }
        array_walk($indices, function ($index) use (&$shortestPathLength, $i)  {
          $shortestPathLength[] = abs($index - $i);
        });
        $matrix[$event][$path[$i]] = ($matrix[$event][$path[$i]] > 0 ? min($matrix[$event][$path[$i]], min($shortestPathLength)) : min($shortestPathLength));
      }
    }

    //    $unique_clicks = array_unique($click);
    //    foreach ($unique_clicks as $u => $event) {
    //      $indices = array_keys($click, $event);
    //
    //      for ($i = 0; $i < count($click); $i++) {
    //        $shortestPathLength = array();
    //        if ($event == $click[$i]) {
    //          continue;
    //        }
    //        array_walk($indices, function ($index) use (&$shortestPathLength, $i)  {
    //          $shortestPathLength[] = abs($index - $i);
    //        });
    //        $matrix[$event][$click[$i]] = ($matrix[$event][$click[$i]] > 0 ? min($matrix[$event][$click[$i]], min($shortestPathLength)) : min($shortestPathLength));
    //      }
    //    }

    foreach ($matrix as $r => $row) {
      foreach ($row as $key => $value) {
        if ($r !== $key) {
          if (!empty($order[$r]) && !empty($order[$key])) {
            $matrix[$r][$key] = 1.0; // If the products were ordered together, always give a maximum strength relationship
          } else {
            if ($value) {
              if (!empty($cart[$r]) || !empty($order[$r]) || !empty($cart[$key]) || !empty($order[$key])) {
                $matrix[$r][$key] = min(1.0, $strength($value) * (max(1.0, (max($cartFreq + $orderFreq, $clickFreq) / max(1.0, min($cartFreq + $orderFreq, $clickFreq))))));
              } else {
                $matrix[$r][$key] = $strength($value);
              }
              //              if ( (!empty($cart[$r]) || !empty($order[$r])) && (!empty($cart[$key]) || !empty($order[$key])) ) {
              //                $matrix[$r][$key] = $strength(max(1.0, $value * min($cartFreq, $orderFreq)));
              //              } else if ( ((!empty($cart[$r]) || !empty($order[$r])) && (empty($cart[$key]) && empty($order[$key]))) ||
              //                ((empty($cart[$r]) && empty($order[$r])) && (!empty($cart[$key]) || !empty($order[$key]))) ) {
              //                $matrix[$r][$key] = min(1.0, $strength($value) * (max(1.0, (max($cartFreq + $orderFreq, $clickFreq) / min($cartFreq + $orderFreq, $clickFreq)))));
              //              }
            }

          }
        }
      }
    }

    return $matrix;
  }

  public function mergeMatrices(&$matrix, $merge) {
    if (empty($matrix)) {
      $matrix = $merge;
    } else {
      $unique_events = array_unique(array_merge(array_keys($matrix), array_keys($merge)));
      $new_events = array_diff(array_keys($merge), array_keys($matrix));

      if (!empty($new_events)) {
        foreach ($new_events as $n => $new) {
          foreach ($matrix as $r => $row) {
            $matrix[$r][$new] = 0;
          }
          $matrix[$new] = array_fill_keys($unique_events, 0);
        }
      }
      foreach ($matrix as $r => $row) {
        if (!empty($merge[$r])) {
          foreach ($row as $key => $value) {
            if (!empty($merge[$r][$key])) {
              $matrix[$r][$key] += $merge[$r][$key];
            }
          }
        }
      }
    }
  }

  public function clearModel() {
    $db = \Drupal::database();
    $db->delete($this->pairsTableName)->execute();
    \Drupal::configFactory()->getEditable('jbs_commerce_product_recommendation.settings')->set('lastUpdate', 0)->save();
    \Drupal::messenger()->addMessage('The Recommendation Model has been cleared.');
  }

  public function updateLog($cutoff) {
    $updated = explode(' ', \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp(time(), 'UTC'));
    array_splice($updated, -1);
    \Drupal::database()->insert(\Drupal::config('jbs_commerce_product_recommendation.settings')->get('logTableName'))
      ->fields([
        'cutoff' => $cutoff,
        'timestamp' => implode(' ', $updated),
      ])
      ->execute();
    //\Drupal::messenger()->addMessage('Cutoff = ' . $cutoff);
    \Drupal::configFactory()->getEditable('jbs_commerce_product_recommendation.settings')->set('lastUpdate', $cutoff)->save();
  }

  public function getScores($pid, $range = 0) {
    $query = \Drupal::database()->select($this->pairsTableName, 't');
    $query->fields('t', ['p1', 'p2', 'score']);
    $query->condition(
      $query->orConditionGroup()
        ->condition('p1', $pid, '=')
        ->condition('p2', $pid, '=')
    );
    $query->orderBy('score', 'DESC');
    if (!empty($range)) {
      $query->range(0, $range);
    }
    $result = $query->execute()->fetchAll();
    return $result;
  }
}