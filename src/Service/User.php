<?php

namespace Drupal\okta\Service;

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
   */
  public function __construct(Users $oktaUserService,
                              Apps $oktaAppService,
                              ConfigFactory $config) {
    $this->oktaUserService = $oktaUserService;
    $this->oktaAppService = $oktaAppService;
    $this->config = $config->getEditable('okta.settings');
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
    return $this->oktaAppService->assignUsersToApp($app_id, $credentials);
  }

  /**
   * Gets an Okta user by email address.
   *
   * @param string $email
   *   Email address.
   *
   * @return null|object
   *   Returns the Okta account if it exists.
   */
  public function getUserByEmail($email) {
    $user = $this->oktaUserService->userGetByEmail($email);
    return $user;
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
    // OKTA Default password policy requires passwords to meet a certain criteria.
    // See: https://developer.okta.com/docs/api/resources/policy.html#PasswordComplexityObject
    // This custom implementation should be replaced by password_policy module once the issue below is resolved
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

}