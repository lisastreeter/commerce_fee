# commerce_fee
Commerce Fee submodule

Note: a modification is necessary in commerce file: commerce/src/Plugin/Field/FieldType/PluginItemDeriver.php
  * Modify method getDerivativeDefinitions()
  * add commerce_fee plugin to the $plugin_types array: 'commerce_fee' => $this->t('Commerce fee'),
