<?php

namespace Drupal\Tests\commerce_fee\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_price\Price;
use Drupal\commerce_fee\Entity\Fee;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Tests the fee availability logic.
 *
 * @group commerce
 */
class FeeAvailabilityTest extends CommerceKernelTestBase {

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

    OrderItemType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderType' => 'default',
    ])->save();

    $order_item = OrderItem::create([
      'type' => 'test',
      'quantity' => 1,
      'unit_price' => new Price('12.00', 'USD'),
    ]);
    $order_item->save();
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'store_id' => $this->store,
      'uid' => $this->createUser(),
      'order_items' => [$order_item],
    ]);
    $order->setRefreshState(Order::REFRESH_SKIP);
    $order->save();
    $this->order = $this->reloadEntity($order);
  }

  /**
   * Test general availability.
   */
  public function testAvailability() {
    $fee = Fee::create([
      'order_types' => ['default'],
      'stores' => [$this->store->id()],
      'status' => TRUE,
    ]);
    $fee->save();
    $this->assertTrue($fee->available($this->order));

    $fee->setEnabled(FALSE);
    $this->assertFalse($fee->available($this->order));
    $fee->setEnabled(TRUE);

    $fee->setOrderTypeIds(['test']);
    $this->assertFalse($fee->available($this->order));
    $fee->setOrderTypeIds(['default']);

    $fee->setStoreIds(['90']);
    $this->assertFalse($fee->available($this->order));
    $fee->setStoreIds([$this->store->id()]);
  }

  /**
   * Tests the start date logic.
   */
  public function testStartDate() {
    // Default start date.
    $fee = Fee::create([
      'order_types' => ['default'],
      'stores' => [$this->store->id()],
      'status' => TRUE,
    ]);
    $fee->save();
    $this->assertTrue($fee->available($this->order));

    // The computed ->date property always converts dates to UTC,
    // causing failures around 8PM EST once the UTC date passes midnight.
    $now = (new \DateTime())->setTime(20, 00);
    $this->container->get('request_stack')->getCurrentRequest()->server->set('REQUEST_TIME', $now->getTimestamp());
    $this->assertTrue($fee->available($this->order));

    // Past start date.
    $date = new DrupalDateTime('2017-01-01');
    $fee->setStartDate($date);
    $this->assertTrue($fee->available($this->order));

    // Future start date.
    $date = new DrupalDateTime();
    $date->modify('+1 week');
    $fee->setStartDate($date);
    $this->assertFalse($fee->available($this->order));
  }

  /**
   * Tests the end date logic.
   */
  public function testEndDate() {
    // No end date date.
    $fee = Fee::create([
      'order_types' => ['default'],
      'stores' => [$this->store->id()],
      'status' => TRUE,
    ]);
    $fee->save();
    $this->assertTrue($fee->available($this->order));

    // Past end date.
    $date = new DrupalDateTime('2017-01-01');
    $fee->setEndDate($date);
    $this->assertFalse($fee->available($this->order));

    // Future end date.
    $date = new DrupalDateTime();
    $date->modify('+1 week');
    $fee->setEndDate($date);
    $this->assertTrue($fee->available($this->order));
  }

}
