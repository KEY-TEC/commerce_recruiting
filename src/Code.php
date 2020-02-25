<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Url;

/**
 * Code.
 */
class Code {

  /**
   * The campaign option code.
   *
   * @var string
   */
  private $code;

  /**
   * The recruiter code or id.
   *
   * @var string
   */
  private $recruiterCode;

  /**
   * Create code.
   *
   * @param string $campaign_code
   *   The campaign option code.
   * @param string $recruiter_code
   *   Recruiter code or id.
   *
   * @return \Drupal\commerce_recruiting\Code
   */
  public static function create($campaign_code, $recruiter_code) {
    return new Code($campaign_code, $recruiter_code);
  }

  /**
   * Create a new code instance from a code string.
   * The string has a format X--Y where
   * X = the campaign option code
   * Y = the recruiter uid or the recruiter's personal code.
   *
   * @param string $code_string
   *   Code in format X--Y.
   */
  public static function createFromCode($code_string) {
    $code_array = explode('--', $code_string);
    $recruiter_code = isset($code_array[1]) ? $code_array[1] : NULL;
    return new Code($code_array[0], $recruiter_code);
  }

  /**
   * Code constructor.
   *
   * @param $code
   *   The campaign option code.
   * @param string|null $recruiter_code
   */
  private function __construct($code, $recruiter_code = NULL) {
    $this->code = $code;
    $this->recruiterCode = $recruiter_code;
  }

  /**
   * Generate Code url.
   *
   * @return \Drupal\Core\Url
   *   The url.
   */
  public function url() {
    $code = $this->getCode();
    if ($this->recruiterCode != NULL) {
      $code .= '--' . $this->recruiterCode;
    }
    return Url::fromRoute('commerce_recruitment.recruitment_url', ['campaign_code' => $code], ['absolute' => TRUE]);
  }

  /**
   * Returns the recruiter code or id.
   *
   * @return string
   *   The recruiter code or id.
   */
  public function getRecruiterCode() {
    return $this->recruiterCode;
  }

  /**
   * Returns the campaign option code.
   *
   * @return string
   *   The campaign option code.
   */
  public function getCode() {
    return $this->code;
  }

}
