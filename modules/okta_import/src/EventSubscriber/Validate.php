<?php

namespace Drupal\okta_import\EventSubscriber;

use Drupal\okta_import\Event\ValidateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class Validate implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ValidateEvent::OKTA_IMPORT_VALIDATE] = 'validateSub';
    return $events;
  }

  /**
   * Alter user before validate.
   *
   * @param \Drupal\okta_import\Event\ValidateEvent $event
   *   Validate Event.
   */
  public function validateSub(ValidateEvent $event) {
    $emails = $event->getEmails();
    // ksm($emails);
  }

}
