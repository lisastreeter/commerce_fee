<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce_fee\Entity\FeeInterface as FeeEntityInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for fees.
 *
 * Fees can target the entire order, or individual order items.
 * Therefore, each fee plugin actually implements one of the child interfaces.
 *
 * @see \Drupal\commerce_fee\Plugin\Commerce\Fee\OrderFeeInterface
 * @see \Drupal\commerce_fee\Plugin\Commerce\Fee\OrderItemFeeInterface
 */
interface FeeInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Gets the fee entity type ID.
   *
   * This is the entity type ID of the entity passed to apply().
   *
   * @return string
   *   The fees's entity type ID.
   */
  public function getEntityTypeId();

  /**
   * Applies the fee to the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\commerce_fee\Entity\FeeInterface $fee
   *   The parent fee.
   */
  public function apply(EntityInterface $entity, FeeEntityInterface $fee);

}
