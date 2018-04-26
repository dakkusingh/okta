<?php

namespace Drupal\okta_import\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PreSubmitEvent.
 *
 * @package Drupal\okta_import\Event
 */
class PreSubmitEvent extends Event {

  const OKTA_IMPORT_PRESUBMIT = 'okta_import.presubmit';

  protected $user;

  /**
   * PreSubmitEvent constructor.
   *
   * @param array $user
   *   User.
   */
  public function __construct(array $user) {
    $this->user = $user;
  }

  /**
   * Getter for the user array.
   *
   * @return user
   *   User
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Setter for user array.
   *
   * @param array $user
   *   User.
   */
  public function setUser(array $user) {
    $this->user = $user;
  }

}
