<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Url;

/**
 * Code.
 */
class Code {

  /**
   * The code.
   *
   * @var string
   */
  private $code;

  /**
   * The recruiter.
   *
   * @var string
   */
  private $recruiterId;

  /**
   * Create code.
   *
   * @param string $code
   *   The Code.
   * @param string $recruiter_id
   *   Recruiter id.
   */
  public static function create($code, $recruiter_id) {
    return new Code($code, $recruiter_id);
  }

  /**
   * Create code including recruiter id.
   *
   * @param string $code_string
   *   Code in format XXXX--RUID.
   */
  public static function createFromCode($code_string) {
    $code_array = explode('--', $code_string);
    $recruiter_id = isset($code_array[1]) ? $code_array[1] : NULL;
    return new Code($code_array[0], $recruiter_id);
  }

  /**
   * Code constructor.
   */
  private function __construct($code, $recruiter_id = NULL) {
    $this->code = $code;
    $this->recruiterId = $recruiter_id;
  }

  /**
   * Generate Code url.
   *
   * @return \Drupal\Core\Url
   *   The url.
   */
  public function url() {
    $code = $this->getCode();
    if ($this->recruiterId != NULL) {
      $code .= '--' . $this->recruiterId;
    }
    return Url::fromRoute('commerce_recruiting.campaign_option_code', ['campaign_option_code' => $code], ['absolute' => TRUE]);
  }

  /**
   * The recruiter id.
   *
   * @return string
   *   The recruiter id.
   */
  public function getRecruiterId() {
    return $this->recruiterId;
  }

  /**
   * The code.
   *
   * @return string
   *   The code.
   */
  public function getCode() {
    return $this->code;
  }

}
