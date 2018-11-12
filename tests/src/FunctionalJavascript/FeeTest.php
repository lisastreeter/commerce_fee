<?php

namespace Drupal\Tests\commerce_fee\FunctionalJavascript;

use Drupal\commerce_fee\Entity\Fee;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Tests\commerce\FunctionalJavascript\JavascriptTestTrait;

/**
 * Tests the admin UI for fees.
 *
 * @group commerce
 */
class FeeTest extends CommerceBrowserTestBase {

  use JavascriptTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'path',
    'commerce_product',
    'commerce_fee',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_fee',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests creating a fee.
   */
  public function testCreateFee() {
    $this->drupalGet('admin/commerce/fees');
    $this->getSession()->getPage()->clickLink('Add fee');

    // Check the integrity of the form.
    $this->assertSession()->fieldExists('name[0][value]');
    $name = $this->randomMachineName(8);
    $this->getSession()->getPage()->fillField('name[0][value]', $name);
    $this->getSession()->getPage()->selectFieldOption('plugin[0][target_plugin_id]', 'order_item_percentage');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('plugin[0][target_plugin_configuration][order_item_percentage][percentage]', '10.0');

    // Change, assert any values reset.
    $this->getSession()->getPage()->selectFieldOption('plugin[0][target_plugin_id]', 'order_percentage');
    $this->waitForAjaxToFinish();
    $this->assertSession()->fieldValueNotEquals('plugin[0][target_plugin_configuration][order_percentage][percentage]', '10.0');
    $this->getSession()->getPage()->fillField('plugin[0][target_plugin_configuration][order_percentage][percentage]', '10.0');

    // Confirm the integrity of the conditions UI.
    foreach (['order', 'products', 'customer'] as $condition_group) {
      $tab_matches = $this->xpath('//a[@href="#edit-conditions-form-' . $condition_group . '"]');
      $this->assertNotEmpty($tab_matches);
    }
    $vertical_tab_elements = $this->xpath('//a[@href="#edit-conditions-form-order"]');
    $vertical_tab_element = reset($vertical_tab_elements);
    $vertical_tab_element->click();
    $this->getSession()->getPage()->checkField('Current order total');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->fillField('conditions[form][order][order_total_price][configuration][form][amount][number]', '50.00');

    $this->submitForm([], t('Save'));
    $this->assertSession()->pageTextContains("Saved the $name fee.");
    $fee_count = $this->getSession()->getPage()->find('xpath', '//table/tbody/tr/td[text()="' . $name . '"]');
    $this->assertEquals(count($fee_count), 1, 'fees exists in the table.');

    $fee = Fee::load(1);
    /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItem $plugin_field */
    $plugin_field = $fee->get('plugin')->first();
    $this->assertEquals('0.10', $plugin_field->target_plugin_configuration['percentage']);

    /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItem $condition_field */
    $condition_field = $fee->get('conditions')->first();
    $this->assertEquals('50.00', $condition_field->target_plugin_configuration['amount']['number']);
  }

  /**
   * Tests creating a fee with an end date.
   */
  public function testCreateFeeWithEndDate() {
    $this->drupalGet('admin/commerce/fees');
    $this->getSession()->getPage()->clickLink('Add fee');
    $this->drupalGet('fee/add');

    // Check the integrity of the form.
    $this->assertSession()->fieldExists('name[0][value]');

    $this->getSession()->getPage()->fillField('plugin[0][target_plugin_id]', 'order_percentage');
    $this->waitForAjaxToFinish();

    $name = $this->randomMachineName(8);
    $edit = [
      'name[0][value]' => $name,
      'plugin[0][target_plugin_configuration][order_percentage][percentage]' => '10.0',
    ];

    // Set an end date.
    $this->getSession()->getPage()->checkField('end_date[0][has_value]');
    $edit['end_date[0][container][value][date]'] = date("Y") + 1 . '-01-01';

    $this->submitForm($edit, t('Save'));
    $this->assertSession()->pageTextContains("Saved the $name fee.");
    $fee_count = $this->getSession()->getPage()->find('xpath', '//table/tbody/tr/td[text()="' . $name . '"]');
    $this->assertEquals(count($fee_count), 1, 'fees exists in the table.');

    /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItem $plugin_field */
    $plugin_field = Fee::load(1)->get('plugin')->first();
    $this->assertEquals('0.10', $plugin_field->target_plugin_configuration['percentage']);
  }

  /**
   * Tests editing a fee.
   */
  public function testEditFee() {
    $fee = $this->createEntity('commerce_fee', [
      'name' => $this->randomMachineName(8),
      'status' => TRUE,
      'plugin' => [
        'target_plugin_id' => 'order_item_percentage',
        'target_plugin_configuration' => [
          'percentage' => '0.10',
        ],
      ],
      'conditions' => [
        [
          'target_plugin_id' => 'order_total_price',
          'target_plugin_configuration' => [
            'amount' => [
              'number' => '9.10',
              'currency_code' => 'USD',
            ],
          ],
        ],
      ],
    ]);

    /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItem $plugin_field */
    $plugin_field = $fee->get('plugin')->first();
    $this->assertEquals('0.10', $plugin_field->target_plugin_configuration['percentage']);

    $this->drupalGet($fee->toUrl('edit-form'));
    $this->assertSession()->pageTextContains('Restricted');
    $this->assertSession()->checkboxChecked('Current order total');
    $this->assertSession()->fieldValueEquals('conditions[form][order][order_total_price][configuration][form][amount][number]', '9.10');

    $new_fee_name = $this->randomMachineName(8);
    $edit = [
      'name[0][value]' => $new_fee_name,
      'plugin[0][target_plugin_configuration][order_item_percentage][percentage]' => '20',
    ];
    $this->submitForm($edit, 'Save');

    \Drupal::service('entity_type.manager')->getStorage('commerce_fee')->resetCache([$fee->id()]);
    $fee_changed = Fee::load($fee->id());
    $this->assertEquals($new_fee_name, $fee_changed->getName(), 'The fee name successfully updated.');

    /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItem $plugin_field */
    $plugin_field = $fee_changed->get('plugin')->first();
    $this->assertEquals('0.20', $plugin_field->target_plugin_configuration['percentage']);
  }

  /**
   * Tests deleting a fee.
   */
  public function testDeleteFee() {
    $fee = $this->createEntity('commerce_fee', [
      'name' => $this->randomMachineName(8),
    ]);
    $this->drupalGet($fee->toUrl('delete-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('This action cannot be undone.');
    $this->submitForm([], t('Delete'));

    \Drupal::service('entity_type.manager')->getStorage('commerce_fee')->resetCache([$fee->id()]);
    $fee_exists = (bool) Fee::load($fee->id());
    $this->assertEmpty($fee_exists, 'The new fee has been deleted from the database using UI.');
  }

}
