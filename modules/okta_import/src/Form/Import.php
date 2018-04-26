<?php

namespace Drupal\okta_import\Form;

use Drupal\Core\Form\FormBase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\okta_import\Event\ValidateEvent;
//use Drupal\okta_import\Event\PreSubmitEvent;
//use Drupal\okta_import\Event\PostSubmitEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implements the Okta Import form controller.
 *
 * @package Drupal\okta_import\Form
 */
class Import extends FormBase {

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Import constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(LoggerInterface $logger,
                              EventDispatcherInterface $eventDispatcher) {
    $this->logger = $logger;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')->get('okta_import_import'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'okta_import_import';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'okta_import.import',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['okta_import'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Import users by email'),
    ];

    $form['okta_import']['emails_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Emails List'),
      '#default_value' => '',
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $emails = $form_state->getValue('emails_list');
    // TODO.
    // Allow other modules to subscribe to Validate Event.
//    $validateEvent = new ValidateEvent($indexConfig, $indexName);
//    $event = $this->eventDispatcher->dispatch(ValidateEvent::OKTA_IMPORT_VALIDATE, $validateEvent);
//    $indexConfig = $event->getIndexConfig();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO.
  }
}