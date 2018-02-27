<?php

namespace Drupal\commerce_fee\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the fee annotation object.
 *
 * Plugin namespace: Plugin\Commerce\Fee.
 *
 * @Annotation
 */
class CommerceFee extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The entity type ID.
   *
   * This is the entity type ID of the entity passed to the plugin during execution.
   * For example: 'commerce_order'.
   *
   * @var string
   */
  public $entity_type;

}
