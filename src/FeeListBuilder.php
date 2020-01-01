<?php

namespace Drupal\commerce_fee;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the list builder for fees.
 */
class FeeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $query = $this->storage->getQuery();
    if ($this->limit) {
      $query->pager($this->limit);
    }
    $entity_ids = $query->execute();
    return $this->storage->loadMultiple($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['start_date'] = $this->t('Start date');
    $header['end_date'] = $this->t('End date');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_fee\Entity\FeeInterface $entity */
    $row['name'] = $entity->label();
    if (!$entity->isEnabled()) {
      $row['name'] .= ' (' . $this->t('Disabled') . ')';
    }
    $row['start_date'] = $entity->getStartDate()->format('M jS Y H:i:s');
    $row['end_date'] = $entity->getEndDate() ? $entity->getEndDate()->format('M jS Y H:i:s') : '—';

    return $row + parent::buildRow($entity);
  }

}
