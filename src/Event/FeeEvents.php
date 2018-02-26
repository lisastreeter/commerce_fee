<?php

namespace Drupal\commerce_fee\Event;

final class FeeEvents {

  /**
   * Name of the event fired after loading a fee.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_LOAD = 'commerce_fee.commerce_fee.load';

  /**
   * Name of the event fired after creating a new fee.
   *
   * Fired before the fee is saved.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_CREATE = 'commerce_fee.commerce_fee.create';

  /**
   * Name of the event fired before saving a fee.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_PRESAVE = 'commerce_fee.commerce_fee.presave';

  /**
   * Name of the event fired after saving a new fee.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_INSERT = 'commerce_fee.commerce_fee.insert';

  /**
   * Name of the event fired after saving an existing fee.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_UPDATE = 'commerce_fee.commerce_fee.update';

  /**
   * Name of the event fired before deleting a fee.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_PREDELETE = 'commerce_fee.commerce_fee.predelete';

  /**
   * Name of the event fired after deleting a fee.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_DELETE = 'commerce_fee.commerce_fee.delete';

  /**
   * Name of the event fired after saving a new fee translation.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_TRANSLATION_INSERT = 'commerce_fee.commerce_fee.translation_insert';

  /**
   * Name of the event fired after deleting a fee translation.
   *
   * @Event
   *
   * @see \Drupal\commerce_fee\Event\FeeEvent
   */
  const FEE_TRANSLATION_DELETE = 'commerce_fee.commerce_fee.translation_delete';

}
