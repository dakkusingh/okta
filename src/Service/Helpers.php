<?php

namespace Drupal\okta\Services;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Okta Helpers.
 */
class Helpers {

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
  public function __construct(ConfigFactory $config) {
    $this->config = $config->getEditable('okta.settings');
  }

  /**
   * Generates a random hexadecimal string.
   *
   * @param int $length
   *   The length of the string to return.
   *
   * @return string
   *   Returns the random string.
   */
  protected function getRandomString($length) {
    return bin2hex(openssl_random_pseudo_bytes($length));
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
  protected function checkPasswordIsValid($password, $email) {
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
