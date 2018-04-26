<?php

namespace Drupal\okta_import\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\okta_import\Event\ValidateEvent;
use Drupal\okta_import\Event\PreSubmitEvent;
use Drupal\okta_import\Event\PostSubmitEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\okta\Service\User as OktaUser;

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
   * @param \Drupal\okta\Service\User $oktaUser
   *   Okta User service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(LoggerInterface $logger,
                              EventDispatcherInterface $eventDispatcher,
                              OktaUser $oktaUser,
                              ConfigFactory $config_factory) {
    $this->logger = $logger;
    $this->eventDispatcher = $eventDispatcher;
    $this->oktaUser = $oktaUser;
    $this->okta_config = $config_factory->get('okta.settings');
    $this->okta_import_config = $config_factory->get('okta_import.import');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')->get('okta_import'),
      $container->get('event_dispatcher'),
      $container->get('okta.user'),
      $container->get('config.factory')
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
      '#description' => $this->t('Email addresses, one on each line.'),
    ];

    $form['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#default_value' => $this->okta_config->get('default_password'),
      '#description' => $this->t('Your password must have<ul><li>8 or more characters</li><li>at least one lowercase letter (a-z)</li><li>at least one uppercase letter (A-Z)</li><li>at least one number (0-9)</li></ul>It must not contain part of your email.'),
    ];

    // TODO Add slightly more helpful description.
    $form['question'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Security question'),
      '#required' => TRUE,
      '#default_value' => $this->okta_config->get('default_question'),
      '#description' => $this->t('Default Question. Do not screw this up.'),
    ];

    // TODO Add slightly more helpful description.
    $form['answer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Security password'),
      '#required' => TRUE,
      '#default_value' => $this->okta_config->get('default_answer'),
      '#description' => $this->t('Default Answer. Do not screw this up.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $password = $form['password']['#value'];

    $emailsList = $form_state->getValue('emails_list');
    // Remove line breaks and empty.
    $emails = array_filter(array_map('trim', explode(PHP_EOL, $emailsList)));

    // Check if the password meets our criteria.
    // We are not checking if every.
    // Email supplied is in the password.
    $passwordIsValid = $this->oktaUser->checkPasswordIsValid($password, '');

    if ($passwordIsValid['valid'] == FALSE) {
      $form_state->setError($form['password'], $passwordIsValid['message']);
      return;
    }

    // TODO Check if emails are valid?
    // TODO.
    // Allow other modules to subscribe to Validate Event.
    $validateEvent = new ValidateEvent($emails);
    $event = $this->eventDispatcher->dispatch(ValidateEvent::OKTA_IMPORT_VALIDATE, $validateEvent);
    $emails = $event->getEmails();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $emailsList = $form_state->getValue('emails_list');
    $password = $form_state->getValue('password');
    $question = $form_state->getValue('question');
    $answer = $form_state->getValue('answer');

    // Remove line breaks and empty.
    $emails = array_filter(array_map('trim', explode(PHP_EOL, $emailsList)));

    foreach ($emails as $email) {
      $user = $this->oktaUser->prepareUser($email, $password, $question, $answer);
//      ksm($user);

      // Allow other modules to subscribe to PreSubmit Event.
      $preSubmitEvent = new PreSubmitEvent($user);
      $preEvent = $this->eventDispatcher->dispatch(PreSubmitEvent::OKTA_IMPORT_PRESUBMIT, $preSubmitEvent);
      $user = $preEvent->getUser();
//      ksm($user);

      // TODO Create Okta Users.
      // GO GO GO

      // Allow other modules to subscribe to Post Submit Event.
      $postSubmitEvent = new PostSubmitEvent($user);
      $postEvent = $this->eventDispatcher->dispatch(PostSubmitEvent::OKTA_IMPORT_POSTSUBMIT, $postSubmitEvent);
      $user = $postEvent->getUser();
    }

    // TODO.

  }

}
