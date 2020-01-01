<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the fixed amount fee for orders.
 *
 * The fee is split between order items, to simplify VAT taxes and refunds.
 *
 * @CommerceFee(
 *   id = "order_fixed_amount",
 *   label = @Translation("Fixed amount added to the order total"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderFixedAmount extends OrderFeeBase {

  use FixedAmountTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, FeeInterface $fee) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $subtotal_price = $order->getSubTotalPrice();
    $amount = $this->getAmount();
    if ($subtotal_price->getCurrencyCode() != $amount->getCurrencyCode()) {
      return;
    }
    // Split the amount between order items.
    $amounts = $this->splitter->split($order, $amount);

    foreach ($order->getItems() as $order_item) {
      if (isset($amounts[$order_item->id()])) {
        $order_item->addAdjustment(new Adjustment([
          'type' => 'fee',
          // @todo Change to label from UI when added in #2770731.
          'label' => t('Fee'),
          'amount' => $amounts[$order_item->id()],
          'source_id' => $fee->id(),
        ]));
      }
    }
  }

}
