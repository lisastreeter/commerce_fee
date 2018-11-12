<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the percentage fee for order items.
 *
 * @CommerceFee(
 *   id = "order_item_percentage",
 *   label = @Translation("Percentage of each matching product added to the order total"),
 *   entity_type = "commerce_order_item",
 * )
 */
class OrderItemPercentage extends OrderItemFeeBase {

  use PercentageTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $entity;
    $percentage = $this->getPercentage();
    $adjustment_amount = $order_item->getTotalPrice()->multiply($percentage);
    $adjustment_amount = $this->rounder->round($adjustment_amount);

    $order_item->addAdjustment(new Adjustment([
      'type' => 'fee',
      // @todo Change to label from UI when added in #2770731.
      'label' => t('Fee'),
      'amount' => $adjustment_amount,
      'percentage' => $percentage,
      'source_id' => $fee->id(),
    ]));
  }

}
