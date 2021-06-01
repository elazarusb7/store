<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_product_recommendation\RecommendationModelFunctionsInterface.
 */

namespace Drupal\jbs_commerce_product_recommendation;

/**
 * Interface RecommendationModelFunctionsInterface
 *
 * @package Drupal\jbs_commerce_product_recommendation
 */
interface RecommendationModelFunctionsInterface {

  /**
   * refreshModel() gets data from the events table and runs calculations to get
   * new product pair scores for recommendations
   *
   * @throws \Exception
   */
  public function refreshModel();

  /**
   * updateScores() updates product pair scores to the database based on new
   * scores calculated by computeWeight() in refreshModel()
   *
   * @param $matrix
   *  The main matrix that contains product pairs as a 2D associative array,
   *  where product ids are the keys, and values are the scores/strength between
   *  keys
   * @param $numSessions
   */
  public function updateScores($matrix, $numSessions);

  /**
   * computeWeights() returns a matrix that contains product pair scores,
   * calculated by product distance and inverse frequency
   *
   * @param $events
   *
   * @return array
   *  The 2D associative array that contains product pairs of a single session
   *  where product ids are the keys, and values are scores/strengths
   */
  public function computeWeights($events);

  /**
   * mergeMatrices() merges the product pair matrices created by each session
   * into a single matrix that will eventually contain all product pairs of
   * every product seen during the refreshModel()
   *
   * @param $matrix
   *  The main matrix that contains product pairs as a 2D associative array,
   *  where product ids are the keys, and values are the scores/strength between
   *  keys
   * @param $merge
   *  The matrix that will be merged in to the main matrix
   */
  public function mergeMatrices(&$matrix, $merge);

  /**
   * clearModel() drops the pairs table, wiping all pairs data and recommendations
   */
  public function clearModel();

  /**
   * updateLog() add the model update to the log table
   *
   * @param $cutoff
   *  Cutoff timestamp of the last event used in refreshModel()
   */
  public function updateLog($cutoff);

  /**
   * getScores() returns the pairs for a particular product id.
   *
   * @param $pid
   * @param $range
   *
   * @return array
   */
  public function getScores($pid, $range);
}
