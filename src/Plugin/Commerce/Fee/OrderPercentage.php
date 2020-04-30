<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the percentage fee for orders.
 *
 * The fee is split between order items, to simplify VAT taxes and refunds.
 *
 * @CommerceFee(
 *   id = "order_percentage",
 *   label = @Translation("Percentage of order subtotal added to the order total"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderPercentage extends OrderFeeBase {

  use PercentageTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $percentage = $this->getPercentage();
    // Calculate the order-level fee and split it between order items.
    $amount = $order->getSubtotalPrice()->multiply($percentage);
    $amount = $this->rounder->round($amount);
    $amounts = $this->splitter->split($order, $amount, $percentage);

    foreach ($order->getItems() as $order_item) {
      if (isset($amounts[$order_item->id()])) {
        $order_item->addAdjustment(new Adjustment([
          'type' => 'fee',
          'label' => $promotion->getDisplayName() ?: $this->t('Fee'),
          'amount' => $amounts[$order_item->id()],
          'percentage' => $percentage,
          'source_id' => $fee->id(),
        ]));
      }
    }
  }

}
