<?php

namespace Drupal\commerce_fee\Plugin\Commerce\FeePolicy;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the percentage policy for orders.
 *
 * @CommerceFeePolicy(
 *   id = "order_percentage",
 *   label = @Translation("Percentage of order subtotal added to the order total"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderPercentage extends PercentageBase {

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $adjustment_amount = $order->getSubtotalPrice()->multiply($this->getPercentage());
    $adjustment_amount = $this->rounder->round($adjustment_amount);

    $order->addAdjustment(new Adjustment([
      'type' => 'fee',
      // @todo Change to label from UI when added in #2770731.
      'label' => t('Discount'),
      'amount' => $adjustment_amount,
      'percentage' => $this->getPercentage(),
      'source_id' => $fee->id(),
    ]));
  }

}
