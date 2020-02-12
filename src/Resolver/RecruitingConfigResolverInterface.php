<?php

namespace Drupal\commerce_recruitment\Resolver;

/**
 * Defines the interface for recruiting config resolvers.
 */
interface RecruitingConfigResolverInterface {

  /**
   * Resolves the recruiting config.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfigInterface|null
   *   The recruiting config, if resolved. Otherwise NULL, indicating that the next
   *   resolver in the chain should be called.
   */
  public function resolve();

}
