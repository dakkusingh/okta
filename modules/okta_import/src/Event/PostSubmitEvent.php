<?php

namespace Drupal\okta_import\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostSubmitEvent.
 *
 * @package Drupal\okta_import\Event
 */
class PostSubmitEvent extends Event {

  const OKTA_IMPORT_POSTSUBMIT = 'okta_import.postsubmit';

  protected $user;

  /**
   * PostSubmitEvent constructor.
   *
   * @param $email
   */
  public function __construct($user) {
    $this->user = $user;
  }

  /**
   * Getter for the user array.
   *
   * @return user
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Setter for user array.
   *
   * @param $user
   */
  public function setUser($user) {
    $this->user = $user;
  }

}
