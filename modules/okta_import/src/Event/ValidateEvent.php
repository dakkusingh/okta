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
   * @param array $emails
   *   Email Addresses.
   */
  public function __construct(array $emails) {
    $this->emails = $emails;
  }

  /**
   * Getter for the emails array.
   *
   * @return emails
   *   Email Addresses.
   */
  public function getEmails() {
    return $this->emails;
  }

  /**
   * Setter for emails array.
   *
   * @param array $emails
   *   Email Addresses.
   */
  public function setEmails(array $emails) {
    $this->emails = $emails;
  }

}
