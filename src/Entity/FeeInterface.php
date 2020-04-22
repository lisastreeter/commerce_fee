<?php

namespace Drupal\commerce_fee\Entity;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_store\Entity\EntityStoresInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\commerce_fee\Plugin\Commerce\Fee\FeeInterface as FeePluginInterface;

/**
 * Defines the interface for fees.
 */
interface FeeInterface extends ContentEntityInterface, EntityStoresInterface {

  /**
   * Gets the fee name.
   *
   * This name is admin-facing.
   *
   * @return string
   *   The fee name.
   */
  public function getName();

  /**
   * Sets the fee name.
   *
   * @param string $name
   *   The fee name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the fee display name.
   *
   * This name is user-facing.
   * Shown in the order total summary.
   *
   * @return string
   *   The fee display name. If empty, use t('Fee').
   */
  public function getDisplayName();

  /**
   * Sets the fee display name.
   *
   * @param string $display_name
   *   The fee display name.
   *
   * @return $this
   */
  public function setDisplayName($display_name);

  /**
   * Gets the fee description.
   *
   * @return string
   *   The fee description.
   */
  public function getDescription();

  /**
   * Sets the fee description.
   *
   * @param string $description
   *   The fee description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Gets the fee order types.
   *
   * @return \Drupal\commerce_order\Entity\OrderTypeInterface[]
   *   The fee order types.
   */
  public function getOrderTypes();

  /**
   * Sets the fee order types.
   *
   * @param \Drupal\commerce_order\Entity\OrderTypeInterface[] $order_types
   *   The fee order types.
   *
   * @return $this
   */
  public function setOrderTypes(array $order_types);

  /**
   * Gets the fee order type IDs.
   *
   * @return int[]
   *   The fee order type IDs.
   */
  public function getOrderTypeIds();

  /**
   * Sets the fee order type IDs.
   *
   * @param int[] $order_type_ids
   *   The fee order type IDs.
   *
   * @return $this
   */
  public function setOrderTypeIds(array $order_type_ids);

  /**
   * Gets the plugin.
   *
   * @return \Drupal\commerce_fee\Plugin\Commerce\Fee\FeeInterface|null
   *   The plugin, or NULL if not yet available.
   */
  public function getPlugin();

  /**
   * Sets the plugin.
   *
   * @param \Drupal\commerce_fee\Plugin\Commerce\Fee\FeeInterface $plugin
   *   The plugin.
   *
   * @return $this
   */
  public function setPlugin(FeePluginInterface $plugin);

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

  /**
   * Sets the condition operator.
   *
   * @param string $condition_operator
   *   The condition operator.
   *
   * @return $this
   */
  public function setConditionOperator($condition_operator);

  /**
   * Gets the fee start date/time.
   *
   * The start date/time should always be used in the store timezone.
   * Since the fee can belong to multiple stores, the timezone
   * isn't known at load/save time, and is provided by the caller instead.
   *
   * Note that the returned date/time value is the same in any timezone,
   * the "2019-10-17 10:00" stored value is returned as "2019-10-17 10:00 CET"
   * for "Europe/Berlin" and "2019-10-17 10:00 ET" for "America/New_York".
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The promotion start date/time.
   */
  public function getStartDate($store_timezone = 'UTC');

  /**
   * Sets the fee start date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
   *   The fee start date/time.
   *
   * @return $this
   */
  public function setStartDate(DrupalDateTime $start_date);

  /**
   * Gets the fee end date/time.
   *
   * The end date/time should always be used in the store timezone.
   * Since the promotion can belong to multiple stores, the timezone
   * isn't known at load/save time, and is provided by the caller instead.
   *
   * Note that the returned date/time value is the same in any timezone,
   * the "2019-10-17 11:00" stored value is returned as "2019-10-17 11:00 CET"
   * for "Europe/Berlin" and "2019-10-17 11:00 ET" for "America/New_York".
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The fee end date/time.
   */
  public function getEndDate($store_timezone = 'UTC');

  /**
   * Sets the fee end date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
   *   The fee end date/time.
   *
   * @return $this
   */
  public function setEndDate(DrupalDateTime $end_date = NULL);

  /**
   * Get whether the fee is enabled.
   *
   * @return bool
   *   TRUE if the fee is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the fee is enabled.
   *
   * @param bool $enabled
   *   Whether the fee is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

  /**
   * Checks whether the fee is available for the given order.
   *
   * Ensures that the order type and store match the fee's,
   * that the fee is enabled, and that the current date
   * matches the start and end dates.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return bool
   *   TRUE if fee is available, FALSE otherwise.
   */
  public function available(OrderInterface $order);

  /**
   * Checks whether the fee can be applied to the given order.
   *
   * Ensures that the conditions pass.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return bool
   *   TRUE if fee can be applied, FALSE otherwise.
   */
  public function applies(OrderInterface $order);

  /**
   * Applies the fee to the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   */
  public function apply(OrderInterface $order);

}
