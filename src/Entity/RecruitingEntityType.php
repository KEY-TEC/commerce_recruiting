<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the recruiting entity type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_recruiting_type",
 *   label = @Translation("Recruitings"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_recruitment\RecruitingEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_recruitment\Form\RecruitingEntityTypeForm",
 *       "edit" = "Drupal\commerce_recruitment\Form\RecruitingEntityTypeForm",
 *       "delete" = "Drupal\commerce_recruitment\Form\RecruitingEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_recruitment\RecruitingEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_recruiting_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "commerce_recruiting",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/commerce_recruiting_type/{commerce_recruiting_type}",
 *     "add-form" = "/admin/commerce/config/commerce_recruiting_type/add",
 *     "edit-form" = "/admin/commerce/config/commerce_recruiting_type/{commerce_recruiting_type}/edit",
 *     "delete-form" = "/admin/commerce/config/commerce_recruiting_type/{commerce_recruiting_type}/delete",
 *     "collection" = "/admin/commerce/config/commerce_recruiting_type"
 *   }
 * )
 */
class RecruitingEntityType extends ConfigEntityBundleBase implements RecruitingEntityTypeInterface {

  /**
   * The recruiting entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The recruiting entity type label.
   *
   * @var string
   */
  protected $label;

}
