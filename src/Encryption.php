<?php

namespace Drupal\commerce_recruitment;

/**
 * Class Encryption.
 *
 * @package Drupal\commerce_recruitment
 */
class Encryption {

  const SLASH_REPLACEMENT = '%%$';

  /**
   * Encrypt string.
   *
   * @param string
   *   The string to encrypt.
   *
   * @return string
   *   The encrypted string.
   */
  public static function encrypt($data) {
    $result = base64_encode($data);
    $result = urlencode($result);
    $result = str_replace('/', Encryption::SLASH_REPLACEMENT, $result);
    $result = str_replace('%', '!', $result);
    return $result;
  }

  /**
   * Decrypt an encrypted string.
   *
   * @param string
   *   The encrypted string.
   *
   * @return string
   *   The decrypted string.
   */
  public static function decrypt($encrypted_data) {
    $encrypted_data = str_replace('!', '%', $encrypted_data);
    $encrypted_data = str_replace(Encryption::SLASH_REPLACEMENT, '/', $encrypted_data);
    $encrypted_data = urldecode($encrypted_data);
    $encrypted_data = base64_decode($encrypted_data);
    return $encrypted_data;
  }

}
