<?php

namespace Drupal\okta\Service;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\okta_api\Service\Users;
use Drupal\okta_api\Service\Apps;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service for provisioning Okta users as part of the signup process.
 */
class User {

  protected $oktaUserService;
  protected $oktaAppService;
  protected $config;
  protected $loggerFactory;

  use StringTranslationTrait;

  /**
   * CqcUserService constructor.
   *
   * @param \Drupal\okta_api\Service\Users $oktaUserService
   *   An instance of okta_api Users service.
   * @param \Drupal\okta_api\Service\Apps $oktaAppService
   *   An instance of okta_api Apps service.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   An instance of Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   LoggerChannelFactory.
   */
  public function __construct(Users $oktaUserService,
                              Apps $oktaAppService,
                              ConfigFactory $config,
                              LoggerChannelFactory $loggerFactory) {
    $this->oktaUserService = $oktaUserService;
    $this->oktaAppService = $oktaAppService;
    $this->config = $config->getEditable('okta.settings');
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Creates a user in Okta.
   */
  public function createUser($firstName, $lastName, $email, $password) {
    $profile = $this->oktaUserService->buildProfile($firstName, $lastName, $email, $email);

    $credentials = $this->oktaUserService->buildCredentials(
      $password, [
        'question' => $this->config->get('default_answer'),
        'answer' => $this->config->get('default_answer'),
      ]);

    $newUser = $this->oktaUserService->userCreate($profile, $credentials, NULL, FALSE);

    return $newUser;
  }

  /**
   * Adds an Okta user to an app.
   */
  public function addUserToApp($user) {
    $credentials = [
      'id' => $user->id,
      'scope' => 'USER',
      'credentials' => ['userName' => $user->profile->email],
    ];

    $app_id = $this->config->get('default_app_id');
    $addToOktaApp = $this->oktaAppService->assignUsersToApp($app_id, $credentials);

    if ($addToOktaApp != FALSE) {
      // Log success.
      $this->loggerFactory->get('okta')->error("@message", ['@message' => 'Assigned app to user: ' . $user->profile->email]);
    }
    else {
      // Log fail.
      $this->loggerFactory->get('okta')->error("@message", ['@message' => 'Failed to assign app to user: ' . $user->profile->email]);
    }

    return $addToOktaApp;
  }

  /**
   * Checks that the user's password is valid.
   *
   * @param string $password
   *   Password.
   * @param string $email
   *   Email.
   *
   * @return array
   *   Returns TRUE if the password is valid, FALSE if not.
   */
  public function checkPasswordIsValid($password, $email) {
    // OKTA Default password policy requires
    // passwords to meet a certain criteria.
    // See: https://developer.okta.com/docs/api/resources/policy.html#PasswordComplexityObject
    // This custom implementation should be replaced by password_policy
    // module once the issue below is resolved
    // https://www.drupal.org/project/password_policy/issues/2924009
    // https://www.drupal.org/project/password_policy/issues/2562481
    // See:
    // http://cgit.drupalcode.org/password_policy/tree/password_policy_length/src/Plugin/PasswordConstraint/PasswordLength.php?h=8.x-3.x
    if (strlen($password) < 8) {
      return [
        'valid' => FALSE,
        'message' => $this->t('Password length must be at least 8 characters.'),
      ];
    }

    // See:
    // http://cgit.drupalcode.org/password_policy/tree/password_policy_character_types/src/Plugin/PasswordConstraint/CharacterTypes.php?h=8.x-3.x
    $character_sets = count(array_filter([
      preg_match('/[a-z]/', $password),
      preg_match('/[A-Z]/', $password),
      preg_match('/[0-9]/', $password),
    ]));
    if ($character_sets < 3) {
      return [
        'valid' => FALSE,
        'message' => $this->t('Password must contain at least 1 types of character of: lowercase letters, uppercase letters, digits.'),
      ];
    }

    // See:
    // http://cgit.drupalcode.org/password_policy/tree/password_policy_username/src/Plugin/PasswordConstraint/PasswordUsername.php?h=8.x-3.x
    if (stripos($password, $email) !== FALSE) {
      return [
        'valid' => FALSE,
        'message' => $this->t('Password must not contain the email address.'),
      ];
    }

    return ['valid' => TRUE];
  }

  /**
   * Prepare Okta User.
   *
   * @param string $email
   *   Email.
   * @param string $password
   *   Pass.
   * @param string $question
   *   Default Question.
   * @param string $answer
   *   Default Answer.
   * @param string $firstName
   *   First Name.
   * @param string $lastName
   *   Last Name.
   *
   * @return array
   *   Okta User array.
   */
  public function prepareUser($email,
                              $password = '',
                              $question = '',
                              $answer = '',
                              $firstName = '',
                              $lastName = '') {
    // Default FName?
    if ($firstName == '') {
      $firstName = $this->config->get('default_fname');
    }

    // Default LName?
    if ($lastName == '') {
      $lastName = $this->config->get('default_lname');
    }

    // Default Password?
    if ($password == '') {
      $password = $this->config->get('default_password');
    }

    // Default Question?
    if ($question == '') {
      $question = $this->config->get('default_question');
    }

    // Default Answer?
    if ($answer == '') {
      $answer = $this->config->get('default_answer');
    }

    // Create the profile.
    $profile = $this->oktaUserService->buildProfile($firstName, $lastName, $email, $email);

    // Create the credentials.
    $credentials = $this->oktaUserService->buildCredentials(
      $password,
      [
        'question' => $question,
        'answer' => $answer,
      ]
    );

    $user = [
      'profile' => $profile,
      'credentials' => $credentials,
      'already_registered' => FALSE,
      'skip_register' => FALSE,
    ];

    return $user;
  }

  /**
   * Checks whether a user already has an Okta account.
   *
   * @param string $email
   *   Email address.
   *
   * @return bool
   *   Returns TRUE if the user has an Okta account.
   */
  public function checkOktaAccountExists($email) {
    $user = $this->oktaUserService->userGetByEmail($email);
    return isset($user);
  }

  /**
   * Register New OKTA User
   *
   * @param array $user
   * User to create.
   * @param null $provider
   * Provider
   * @param bool $activate
   * Activate
   *
   * @return bool|object
   */
  public function registerNewOktaUser(array $user, $provider = NULL, $activate = FALSE) {
    // Attempt to create the user in OKTA.
    $newUser = $this->oktaUserService->userCreate($user['profile'], $user['credentials'], $provider, $activate);

    if ($newUser != FALSE) {
      // Log user create success.
      $this->loggerFactory->get('okta')->error("@message", ['@message' => 'created user: ' . $user['profile']['email']]);
    }
    else {
      // Log user create fail.
      $this->loggerFactory->get('okta')->error("@message", ['@message' => 'failed to create user: ' . $user['profile']['email']]);
    }

    return $newUser;
  }
}
