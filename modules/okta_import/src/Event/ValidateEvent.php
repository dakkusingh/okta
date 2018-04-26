<?php

namespace Drupal\okta_import\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ValidateEvent.
 *
 * @package Drupal\okta_import\Event
 */
class ValidateEvent extends Event {

  const OKTA_IMPORT_VALIDATE = 'okta_import.validate';

  protected $emails;

  /**
   * ValidateEvent constructor.
   *
   * @param $email
   */
  public function __construct($emails) {
    $this->emails = $emails;
  }

  /**
   * Getter for the emails array.
   *
   * @return emails
   */
  public function getEmails() {
    return $this->emails;
  }

  /**
   * Setter for emails array.
   *
   * @param $emails
   */
  public function setEmails($emails) {
    $this->emails = $emails;
  }

}
