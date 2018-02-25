<?php

namespace Drupal\Tests\commerce_fee\Kernel;

use Drupal\commerce_fee\Entity\Fee;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Tests fees in a multilingual context.
 *
 * @group commerce
 */
class FeeMultilingualTest extends CommerceKernelTestBase {

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
    'commerce_fee',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('commerce_fee');
    ConfigurableLanguage::createFromLangcode('fr')->save();
    ConfigurableLanguage::createFromLangcode('sr')->save();
  }

  /**
   * Tests that a fee returns stores in current language.
   */
  public function testFeestores() {
    $this->container->get('content_translation.manager')
      ->setEnabled('commerce_store', 'online', TRUE);
    $this->store = $this->reloadEntity($this->store);
    $this->store->addTranslation('fr', [
      'name' => 'Magasin par défaut',
    ])->save();

    // Starts now, enabled. No end time.
    $fee = Fee::create([
      'name' => 'Fee 1',
      'order_types' => 'default',
      'stores' => [$this->store->id()],
      'status' => TRUE,
    ]);

    $stores = $fee->getStores();
    $this->assertEquals('Default store', reset($stores)->label());

    $this->config('system.site')->set('default_langcode', 'fr')->save();
    $stores = $fee->getStores();
    $this->assertEquals('Magasin par défaut', reset($stores)->label());

    // Change the default site language and ensure the store is returned
    // even if it has not been translated to that language.
    $this->config('system.site')->set('default_langcode', 'sr')->save();
    $stores = $fee->getStores();
    $this->assertEquals('Default store', reset($stores)->label());
  }

}
