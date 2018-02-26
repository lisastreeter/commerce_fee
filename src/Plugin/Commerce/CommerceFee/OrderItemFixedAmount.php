<?php

namespace Drupal\commerce_fee\Plugin\Commerce\CommerceFee;

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
class OrderItemFixedAmount extends FixedAmountBase {

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $entity;
    $unit_price = $order_item->getUnitPrice();
    $adjustment_amount = $this->getAmount();
    if ($unit_price->getCurrencyCode() != $adjustment_amount->getCurrencyCode()) {
      return;
    }

    $order_item->addAdjustment(new Adjustment([
      'type' => 'fee',
      // @todo Change to label from UI when added in #2770731.
      'label' => t('Fee'),
      'amount' => $adjustment_amount,
      'source_id' => $fee->id(),
    ]));
  }

}
