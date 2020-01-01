<?php

namespace Drupal\commerce_fee\Entity;

use Drupal\commerce\ConditionGroup;
use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface;
use Drupal\commerce\Plugin\Commerce\Condition\ParentEntityAwareInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_fee\Plugin\Commerce\Fee\OrderItemFeeInterface;
use Drupal\commerce_fee\Plugin\Commerce\Fee\FeeInterface as FeePluginInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Defines the fee entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_fee",
 *   label = @Translation("Fee", context = "Commerce"),
 *   label_collection = @Translation("Fees", context = "Commerce"),
 *   label_singular = @Translation("fee", context = "Commerce"),
 *   label_plural = @Translation("fees", context = "Commerce"),
 *   label_count = @PluralTranslation(
 *     singular = "@count fee",
 *     plural = "@count fees",
 *     context = "Commerce",
 *   ),
 *   handlers = {
 *     "event" = "Drupal\commerce_fee\Event\FeeEvent",
 *     "storage" = "Drupal\commerce_fee\FeeStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_fee\FeeListBuilder",
 *     "views_data" = "Drupal\commerce_fee\FeeViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_fee\Form\FeeForm",
 *       "add" = "Drupal\commerce_fee\Form\FeeForm",
 *       "edit" = "Drupal\commerce_fee\Form\FeeForm",
 *       "duplicate" = "Drupal\commerce_fee\Form\FeeForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "commerce_fee",
 *   data_table = "commerce_fee_field_data",
 *   admin_permission = "administer commerce_fee",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "fee_id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/fee/add",
 *     "edit-form" = "/fee/{commerce_fee}/edit",
 *     "duplicate-form" = "/fee/{commerce_fee}/duplicate",
 *     "delete-form" = "/fee/{commerce_fee}/delete",
 *     "delete-multiple-form" = "/admin/commerce/fees/delete",
 *     "collection" = "/admin/commerce/fees",
 *   },
 * )
 */
class Fee extends CommerceContentEntityBase implements FeeInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderTypes() {
    return $this->get('order_types')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderTypes(array $order_types) {
    $this->set('order_types', $order_types);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderTypeIds() {
    $order_type_ids = [];
    foreach ($this->get('order_types') as $field_item) {
      $order_type_ids[] = $field_item->target_id;
    }
    return $order_type_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderTypeIds(array $order_type_ids) {
    $this->set('order_types', $order_type_ids);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStores() {
    return $this->getTranslatedReferencedEntities('stores');
  }

  /**
   * {@inheritdoc}
   */
  public function setStores(array $stores) {
    $this->set('stores', $stores);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreIds() {
    $store_ids = [];
    foreach ($this->get('stores') as $field_item) {
      $store_ids[] = $field_item->target_id;
    }
    return $store_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function setStoreIds(array $store_ids) {
    $this->set('stores', $store_ids);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    if (!$this->get('plugin')->isEmpty()) {
      return $this->get('plugin')->first()->getTargetInstance();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin(FeePluginInterface $plugin) {
    $this->set('plugin', [
      'target_plugin_id' => $plugin->getPluginId(),
      'target_plugin_configuration' => $plugin->getConfiguration(),
    ]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    $conditions = [];
    foreach ($this->get('conditions') as $field_item) {
      /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItemInterface $field_item */
      $condition = $field_item->getTargetInstance();
      if ($condition instanceof ParentEntityAwareInterface) {
        $condition->setParentEntity($this);
      }
      $conditions[] = $condition;
    }
    return $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions(array $conditions) {
    $this->set('conditions', []);
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionInterface) {
        $this->get('conditions')->appendItem([
          'target_plugin_id' => $condition->getPluginId(),
          'target_plugin_configuration' => $condition->getConfiguration(),
        ]);
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditionOperator() {
    return $this->get('condition_operator')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditionOperator($condition_operator) {
    $this->set('condition_operator', $condition_operator);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate($store_timezone = 'UTC') {
    return new DrupalDateTime($this->get('start_date')->value, $store_timezone);
  }

  /**
   * {@inheritdoc}
   */
  public function setStartDate(DrupalDateTime $start_date) {
    $this->get('start_date')->value = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate($store_timezone = 'UTC') {
    if (!$this->get('end_date')->isEmpty()) {
      return new DrupalDateTime($this->get('end_date')->value, $store_timezone);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setEndDate(DrupalDateTime $end_date = NULL) {
    $this->get('end_date')->value = NULL;
    if ($end_date) {
      $this->get('end_date')->value = $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->set('status', (bool) $enabled);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function available(OrderInterface $order) {
    if (!$this->isEnabled()) {
      return FALSE;
    }
    if (!in_array($order->bundle(), $this->getOrderTypeIds())) {
      return FALSE;
    }
    if (!in_array($order->getStoreId(), $this->getStoreIds())) {
      return FALSE;
    }
    $date = $order->getCalculationDate();
    $store_timezone = $date->getTimezone()->getName();
    $start_date = $this->getStartDate($store_timezone);
    if ($start_date->format('U') > $date->format('U')) {
      return FALSE;
    }
    $end_date = $this->getEndDate($store_timezone);
    if ($end_date && $end_date->format('U') <= $date->format('U')) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(OrderInterface $order) {
    $conditions = $this->getConditions();
    if (!$conditions) {
      // Fees without conditions always apply.
      return TRUE;
    }
    $condition_group = new ConditionGroup($conditions, $this->getConditionOperator());

    return $condition_group->evaluate($order);
  }

  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {
    $plugin = $this->getPlugin();
    if ($plugin instanceof OrderItemFeeInterface) {
      $fee_conditions = new ConditionGroup($plugin->getConditions(), 'OR');
      // Apply the plugin to order items that pass the conditions.
      foreach ($order->getItems() as $order_item) {
        if ($fee_conditions->evaluate($order_item)) {
          $plugin->apply($order_item, $this);
        }
      }
    }
    else {
      $plugin->apply($order, $this);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The fee name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('Additional information about the fee to show to the customer'))
      ->setTranslatable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 1,
        'settings' => [
          'rows' => 3,
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['order_types'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order types'))
      ->setDescription(t('The order types for which the fee is valid.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'commerce_order_type')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_entity_select',
        'weight' => 2,
      ]);

    $fields['stores'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Stores'))
      ->setDescription(t('The stores for which the fee is valid.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'commerce_store')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_entity_select',
        'weight' => 2,
      ]);

    $fields['plugin'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_fee')
      ->setLabel(t('Fee type'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_radios',
        'weight' => 3,
      ]);

    $fields['conditions'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_condition')
      ->setLabel(t('Conditions'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_conditions',
        'weight' => 3,
        'settings' => [
          'entity_types' => ['commerce_order'],
        ],
      ]);

    $fields['condition_operator'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Condition operator'))
      ->setDescription(t('The condition operator.'))
      ->setRequired(TRUE)
      ->setSetting('allowed_values', [
        'AND' => t('All conditions must pass'),
        'OR' => t('Only one condition must pass'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue('AND');

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The date the fee becomes valid.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDefaultValueCallback('Drupal\commerce_fee\Entity\Fee::getDefaultStartDate')
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 5,
      ]);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The date after which the fee is invalid.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'datetime')
      ->setSetting('datetime_optional_label', t('Provide an end date'))
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 6,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the fee is enabled.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'on_label' => t('Enabled'),
        'off_label' => t('Disabled'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 0,
      ]);

    return $fields;
  }

  /**
   * Default value callback for 'start_date' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return string
   *   The default value (date string).
   */
  public static function getDefaultStartDate() {
    $timestamp = \Drupal::time()->getRequestTime();
    return gmdate(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $timestamp);
  }

}
