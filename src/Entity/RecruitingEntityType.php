<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the recruiting entity type entity.
 *
 * @ConfigEntityType(
 *   id = "recruiting_type",
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
 *   config_prefix = "recruiting_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "recruiting",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/recruiting_type/{recruiting_type}",
 *     "add-form" = "/admin/structure/recruiting_type/add",
 *     "edit-form" = "/admin/structure/recruiting_type/{recruiting_type}/edit",
 *     "delete-form" = "/admin/structure/recruiting_type/{recruiting_type}/delete",
 *     "collection" = "/admin/structure/recruiting_type"
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
