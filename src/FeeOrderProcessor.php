<?php

namespace Drupal\commerce_fee;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Applies fees to orders during the order refresh process.
 */
class FeeOrderProcessor implements OrderProcessorInterface {

  /**
   * The fee storage.
   *
   * @var \Drupal\commerce_fee\FeeStorageInterface
   */
  protected $feeStorage;

  /**
   * Constructs a new FeeOrderProcessor object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->feeStorage = $entity_type_manager->getStorage('commerce_fee');
  }

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    $fees = $this->feeStorage->loadAvailable($order);
    foreach ($fees as $fee) {
      if ($fee->applies($order)) {
        $fee->apply($order);
      }
    }
  }

}
