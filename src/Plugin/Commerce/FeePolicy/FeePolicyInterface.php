<?php

namespace Drupal\commerce_fee\Plugin\Commerce\FeePolicy;

use Drupal\commerce_fee\Entity\FeeInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for fee policies.
 */
interface FeePolicyInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Gets the policy entity type ID.
   *
   * This is the entity type ID of the entity passed to apply().
   *
   * @return string
   *   The policy's entity type ID.
   */
  public function getEntityTypeId();

  /**
   * Applies the policy to the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\commerce_fee\Entity\FeeInterface $fee
   *   The parent fee.
   */
  public function apply(EntityInterface $entity, FeeInterface $fee);

}
