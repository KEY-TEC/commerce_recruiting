<?php

namespace Drupal\commerce_recruitment\Resolver;

/**
 * Runs the added resolvers one by one until one of them returns a config.
 *
 * Each resolver in the chain can be another chain, which is why this interface
 * extends the recruiting config resolver interface.
 */
interface ChainRecruitingConfigResolverInterface extends RecruitingConfigResolverInterface {

  /**
   * Adds a resolver.
   *
   * @param \Drupal\commerce_recruitment\Resolver\RecruitingConfigResolverInterface $resolver
   *   The resolver.
   */
  public function addResolver(RecruitingConfigResolverInterface $resolver);

  /**
   * Gets all added resolvers.
   *
   * @return \Drupal\commerce_recruitment\Resolver\RecruitingConfigResolverInterface[]
   *   The resolvers.
   */
  public function getResolvers();

}
