<?php

namespace Drupal\commerce_fee\Event;

use Drupal\commerce_fee\Entity\FeeInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the fee event.
 *
 * @see \Drupal\commerce_fee\Event\FeeEvents
 */
class FeeEvent extends Event {

  /**
   * The fee.
   *
   * @var \Drupal\commerce_fee\Entity\FeeInterface
   */
  protected $fee;

  /**
   * Constructs a new FeeEvent.
   *
   * @param \Drupal\commerce_fee\Entity\FeeInterface $fee
   *   The fee.
   */
  public function __construct(FeeInterface $fee) {
    $this->fee = $fee;
  }

  /**
   * Gets the fee.
   *
   * @return \Drupal\commerce_fee\Entity\FeeInterface
   *   The fee.
   */
  public function getFee() {
    return $this->fee;
  }

}
