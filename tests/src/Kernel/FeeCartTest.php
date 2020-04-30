<?php

namespace Drupal\Tests\commerce_fee\Kernel;

use Drupal\commerce_fee\Entity\Fee;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Tests\commerce_cart\Kernel\CartKernelTestBase;

/**
 * Tests the integration between fees and carts.
 *
 * @group commerce
 */
class FeeCartTest extends CartKernelTestBase {

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
    'path',
    'commerce_product',
    'commerce_fee',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_fee');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product');
    $this->installConfig([
      'profile',
      'commerce_order',
      'commerce_product',
      'commerce_fee',
    ]);
  }

  /**
   * Tests adding a product with a fee to the cart.
   */
  public function testFeeCart() {
    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => strtolower($this->randomMachineName()),
      'price' => [
        'number' => '10.00',
        'currency_code' => 'USD',
      ],
    ]);
    $variation->save();
    $product = Product::create([
      'type' => 'default',
      'title' => 'My product',
      'variations' => [$variation],
    ]);
    $product->save();

    $fee = Fee::create([
      'name' => 'Fee test',
      'order_types' => ['default'],
      'stores' => [$this->store->id()],
      'plugin' => [
        'target_plugin_id' => 'order_percentage',
        'target_plugin_configuration' => [
          'percentage' => '0.10',
        ],
      ],
      'start_date' => '2017-01-01',
      'status' => TRUE,
    ]);
    $fee->save();

    $user = $this->createUser();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $cart_order */
    $cart = $this->cartProvider->createCart('default', $this->store, $user);
    $this->cartManager->addEntity($cart, $variation);

    $this->assertEquals(1, count($cart->collectAdjustments()));
    $this->assertEquals(new Price('11.00', 'USD'), $cart->getTotalPrice());

    // Disable the fee.
    $fee->setEnabled(FALSE);
    $fee->save();
    $this->container->get('commerce_order.order_refresh')->refresh($cart);
    $this->assertEmpty($cart->getAdjustments());
    $this->assertEquals(new Price('10.00', 'USD'), $cart->getTotalPrice());
  }

}
