<?php

/**
 * @file
 * Post update functions for Fee.
 */

/**
 * Allows fee start and end dates to have a time component.
 */
function commerce_fee_post_update_1(array &$sandbox = NULL) {
  $fee_storage = \Drupal::entityTypeManager()->getStorage('commerce_fee');
  if (!isset($sandbox['current_count'])) {
    $query = $fee_storage->getQuery();
    $sandbox['total_count'] = $query->count()->execute();
    $sandbox['current_count'] = 0;

    if (empty($sandbox['total_count'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $query = $fee_storage->getQuery();
  $query->range($sandbox['current_count'], 50);
  $result = $query->execute();
  if (empty($result)) {
    $sandbox['#finished'] = 1;
    return;
  }

  /** @var \Drupal\commerce_fee\Entity\Fee[] $fees */
  $fees = $fee_storage->loadMultiple($result);
  foreach ($fees as $fee) {
    // Re-set each date to ensure it is stored in the updated format.
    // Increase the end date by a day to match old inclusive loading
    // (where an end date was valid until 23:59:59 of that day).
    $start_date = $fee->getStartDate();
    $end_date = $fee->getEndDate();
    if ($end_date) {
      $end_date = $end_date->modify('+1 day');
    }
    $fee->setStartDate($start_date);
    $fee->setEndDate($end_date);

    $fee->save();
  }

  $sandbox['current_count'] += 50;
  if ($sandbox['current_count'] >= $sandbox['total_count']) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['total_count'] - $sandbox['current_count']) / $sandbox['total_count'];
  }
}
