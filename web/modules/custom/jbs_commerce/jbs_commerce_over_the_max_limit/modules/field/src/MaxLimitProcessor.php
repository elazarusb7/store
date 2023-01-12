<?php

namespace Drupal\field_qty_max_limit;

use Drupal\Core\TypedData\TypedData;

/**
 * Processor used by the MaxLimit field.
 */
class MaxLimitProcessor extends TypedData {

  /**
   * Cached processed level.
   *
   * @var int|null
   */
  protected $maxLimit = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->maxLimit;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    if (is_null($value)) {
      return;
    }
    $this->maxLimit = $value;
  }

}
