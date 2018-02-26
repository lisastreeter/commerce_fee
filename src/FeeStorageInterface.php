<?php

namespace Drupal\commerce_fee;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Defines the interface for fee storage.
 */
interface FeeStorageInterface extends ContentEntityStorageInterface {

  /**
   * Loads the available fees for the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return \Drupal\commerce_fee\Entity\FeeInterface[]
   *   The available fees.
   */
  public function loadAvailable(OrderInterface $order);

}
