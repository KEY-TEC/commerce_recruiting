<?php

namespace Drupal\commerce_recruitment\Resolver;

/**
 * Default implementation of the chain recruiting config resolver.
 */
class ChainRecruitingConfigResolver implements ChainRecruitingConfigResolverInterface {

  /**
   * The resolvers.
   *
   * @var \Drupal\commerce_recruitment\Resolver\RecruitingConfigResolverInterface[]
   */
  protected $resolvers = [];

  /**
   * Constructs a new ChainRecruitingConfigResolver object.
   *
   * @param \Drupal\commerce_recruitment\Resolver\RecruitingConfigResolverInterface[] $resolvers
   *   The resolvers.
   */
  public function __construct(array $resolvers = []) {
    $this->resolvers = $resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function addResolver(RecruitingConfigResolverInterface $resolver) {
    $this->resolvers[] = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function getResolvers() {
    return $this->resolvers;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve() {
    foreach ($this->resolvers as $resolver) {
      $result = $resolver->resolve();
      if ($result) {
        return $result;
      }
    }
  }

}
