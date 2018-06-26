<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\PriceSplitterInterface;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class OrderFixedAmount extends FixedAmountBase {

  /**
   * The price splitter.
   *
   * @var \Drupal\commerce_order\PriceSplitterInterface
   */
  protected $splitter;

  /**
   * Constructs a new OrderFixedAmount object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The pluginId for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The rounder.
   * @param \Drupal\commerce_order\PriceSplitterInterface $splitter
   *   The splitter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RounderInterface $rounder, PriceSplitterInterface $splitter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $rounder);

    $this->splitter = $splitter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('commerce_price.rounder'),
      $container->get('commerce_order.price_splitter')
    );
  }

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
