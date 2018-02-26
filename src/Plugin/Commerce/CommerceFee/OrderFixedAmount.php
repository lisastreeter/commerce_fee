<?php

namespace Drupal\commerce_fee\Plugin\Commerce\CommerceFee;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the fixed amount fee for orders.
 *
 * @CommerceFee(
 *   id = "order_fixed_amount",
 *   label = @Translation("Fixed amount added to the order total"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderFixedAmount extends FixedAmountBase {

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $subtotal_price = $order->getSubTotalPrice();
    $adjustment_amount = $this->getAmount();
    if ($subtotal_price->getCurrencyCode() != $adjustment_amount->getCurrencyCode()) {
      return;
    }

    $order->addAdjustment(new Adjustment([
      'type' => 'fee',
      // @todo Change to label from UI when added in #2770731.
      'label' => t('Fee'),
      'amount' => $adjustment_amount,
      'source_id' => $fee->id(),
    ]));
  }

}
