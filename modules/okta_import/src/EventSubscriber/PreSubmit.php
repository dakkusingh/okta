<?php

namespace Drupal\okta_import\EventSubscriber;

use Drupal\okta_import\Event\PreSubmitEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class PreSubmit implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[PreSubmitEvent::OKTA_IMPORT_PRESUBMIT] = 'preSubmit';
    return $events;
  }

  /**
   * Alter user before pre submit.
   *
   * @param \Drupal\okta_import\Event\PreSubmitEvent $event
   * Pre Submit Event.
   */
  public function preSubmit(PreSubmitEvent $event) {
    $user = $event->getUser();
    $user['profile']['firstName'] = 'Stuart';
//    ksm($user);
    $event->setUser($user);
  }

}