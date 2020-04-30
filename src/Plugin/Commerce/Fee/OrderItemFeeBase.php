<?php

namespace Drupal\commerce_fee\Plugin\Commerce\Fee;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the base class for order item fees.
 */
abstract class OrderItemFeeBase extends FeeBase implements OrderItemFeeInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'conditions' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['conditions'] = [
      '#type' => 'commerce_conditions',
      '#title' => $this->t('Applies to'),
      '#parent_entity_type' => 'commerce_fee',
      '#entity_types' => ['commerce_order_item'],
      '#default_value' => $this->configuration['conditions'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['conditions'] = $values['conditions'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    $plugin_manager = \Drupal::service('plugin.manager.commerce_condition');
    $conditions = [];
    foreach ($this->configuration['conditions'] as $condition) {
      $conditions[] = $plugin_manager->createInstance($condition['plugin'], $condition['configuration']);
    }
    return $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions(array $conditions) {
    $this->configuration['conditions'] = [];
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionInterface) {
        $this->configuration['conditions'][] = [
          'plugin' => $condition->getPluginId(),
          'configuration' => $condition->getConfiguration(),
        ];
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditionOperator() {
    return 'OR';
  }

}
