<?php

namespace Drupal\Tests\commerce_fee\Kernel;

use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_fee\Entity\Fee;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\commerce_order\Entity\Order;

/**
 * Tests fee storage.
 *
 * @group commerce
 */
class FeeStorageTest extends CommerceKernelTestBase {

  /**
   * The fee storage.
   *
   * @var \Drupal\commerce_fee\FeeStorageInterface
   */
  protected $feeStorage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'profile',
    'state_machine',
    'commerce_order',
    'commerce_product',
    'commerce_fee',
  ];

  /**
   * The test order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * The test order type.
   *
   * @var \Drupal\commerce_order\Entity\OrderTypeInterface
   */
  protected $orderType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_fee');
    $this->installConfig([
      'profile',
      'commerce_order',
      'commerce_fee',
    ]);

    $this->feeStorage = $this->container->get('entity_type.manager')->getStorage('commerce_fee');

    $this->orderType = OrderType::load('default');
    $order_item = OrderItem::create([
      'type' => 'default',
      'quantity' => 1,
      'unit_price' => new Price('12.00', 'USD'),
    ]);
    $order_item->save();

    $this->order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'store_id' => $this->store,
      'uid' => $this->createUser(),
      'order_items' => [$order_item],
    ]);
  }

  /**
   * Tests loadAvailable().
   */
  public function testLoadAvailable() {
    // Starts now, enabled. No end time.
    $fee1 = Fee::create([
      'name' => 'Fee 1',
      'order_types' => [$this->orderType],
      'stores' => [$this->store->id()],
      'fee' => [
        'target_plugin_id' => 'order_fixed_amount',
        'target_plugin_configuration' => [
          'amount' => [
            'number' => '25.00',
            'currency_code' => 'USD',
          ],
        ],
      ],
      'start_date' => '2019-11-15T10:14:00',
      'status' => TRUE,
    ]);
    $this->assertEquals(SAVED_NEW, $fee1->save());

    // Starts now, disabled. No end time.
    /** @var \Drupal\commerce_fee\Entity\Fee $fee2 */
    $fee2 = Fee::create([
      'name' => 'Fee 2',
      'order_types' => [$this->orderType],
      'stores' => [$this->store->id()],
      'fee' => [
        'target_plugin_id' => 'order_percentage',
        'target_plugin_configuration' => [
          'percentage' => '0.20',
        ],
      ],
      'start_date' => '2019-01-01T00:00:00',
      'status' => FALSE,
    ]);
    $this->assertEquals(SAVED_NEW, $fee2->save());
    // Jan 2014, enabled. No end time.
    $fee3 = Fee::create([
      'name' => 'Fee 3',
      'order_types' => [$this->orderType],
      'stores' => [$this->store->id()],
      'fee' => [
        'target_plugin_id' => 'order_percentage',
        'target_plugin_configuration' => [
          'percentage' => '0.30',
        ],
      ],
      'start_date' => '2014-01-01T00:00:00',
      'status' => TRUE,
    ]);
    $this->assertEquals(SAVED_NEW, $fee3->save());
    // Start in 1 week, end in 1 year. Enabled.
    $fee4 = Fee::create([
      'name' => 'Fee 4',
      'order_types' => [$this->orderType],
      'stores' => [$this->store->id()],
      'status' => TRUE,
      'fee' => [
        'target_plugin_id' => 'order_percentage',
        'target_plugin_configuration' => [
          'percentage' => '0.40',
        ],
      ],
      'start_date' => '2019-01-01T00:00:00',
      'end_date' => '2019-11-15T10:14:00',
      'status' => TRUE,
    ]);
    $this->assertEquals(SAVED_NEW, $fee4->save());

    // Verify valid fees load.
    $valid_fees = $this->feeStorage->loadAvailable($this->order);
    $this->assertEquals(2, count($valid_fees));

    // Move the 4th fee's start week to a week ago, makes it valid.
    $fee4->setStartDate(new DrupalDateTime('-1 week'));
    $fee4->save();

    $valid_fees = $this->feeStorage->loadAvailable($this->order);
    $this->assertEquals(3, count($valid_fees));

    // Set fee 3's end date six months ago, making it invalid.
    $fee3->setEndDate(new DrupalDateTime('-6 month'));
    $fee3->save();

    $valid_fees = $this->feeStorage->loadAvailable($this->order);
    $this->assertEquals(2, count($valid_fees));
  }

}
