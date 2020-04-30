<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the fixed amount fee for order items.
 *
 * @CommerceFee(
 *   id = "order_item_fixed_amount",
 *   label = @Translation("Fixed amount added for each matching product"),
 *   entity_type = "commerce_order_item",
 * )
 */
class OrderItemFixedAmount extends OrderItemFeeBase {

  use FixedAmountTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $entity;
    $adjusted_total_price = $order_item->getAdjustedTotalPrice(['fee']);
    $amount = $this->getAmount();
    if ($adjusted_total_price->getCurrencyCode() != $amount->getCurrencyCode()) {
      return;
    }
    $adjustment_amount = $amount->multiply($order_item->getQuantity());
    $adjustment_amount = $this->rounder->round($adjustment_amount);

    $order_item->addAdjustment(new Adjustment([
      'type' => 'fee',
      'label' => $fee->getDisplayName() ?: $this->t('Fee'),
      'amount' => $adjustment_amount,
      'source_id' => $fee->id(),
    ]));
  }

}
