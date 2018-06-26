<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\PriceSplitterInterface;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class OrderPercentage extends PercentageBase {

  /**
   * The price splitter.
   *
   * @var \Drupal\commerce_order\PriceSplitterInterface
   */
  protected $splitter;

  /**
   * Constructs a new OrderPercentage object.
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
    $percentage = $this->getPercentage();
    // Calculate the order-level fee and split it between order items.
    $amount = $order->getSubtotalPrice()->multiply($percentage);
    $amount = $this->rounder->round($amount);
    $amounts = $this->splitter->split($order, $amount, $percentage);

    foreach ($order->getItems() as $order_item) {
      if (isset($amounts[$order_item->id()])) {
        $order_item->addAdjustment(new Adjustment([
          'type' => 'fee',
          // @todo Change to label from UI when added in #2770731.
          'label' => t('Fee'),
          'amount' => $amounts[$order_item->id()],
          'percentage' => $this->getPercentage(),
          'source_id' => $fee->id(),
        ]));
      }
    }
  }

}
