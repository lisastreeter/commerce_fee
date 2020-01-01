<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

/**
 * Defines the interface for order item fees.
 *
 * Order item fees have conditions, which are used to determine which
 * order items should be passed to the fee.
 */
interface OrderItemFeeInterface extends FeeInterface {

  /**
   * Gets the conditions.
   *
   * @return \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[]
   *   The conditions.
   */
  public function getConditions();

  /**
   * Sets the conditions.
   *
   * @param \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[] $conditions
   *   The conditions.
   *
   * @return $this
   */
  public function setConditions(array $conditions);

  /**
   * Gets the condition operator.
   *
   * @return string
   *   The condition operator. Possible values: AND, OR.
   */
  public function getConditionOperator();

}
