<?php

namespace Drupal\okta_import\EventSubscriber;

use Drupal\okta_import\Event\PostSubmitEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class PostSubmit implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[PostSubmitEvent::OKTA_IMPORT_POSTSUBMIT] = 'postSubmit';
    return $events;
  }

  /**
   * Alter user before post submit.
   *
   * @param \Drupal\okta_import\Event\PostSubmitEvent $event
   * Post Submit Event.
   */
  public function postSubmit(PostSubmitEvent $event) {
    $user = $event->getUser();
    ksm($user);
    //$event->setUser($user);
  }

}