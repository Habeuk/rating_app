<?php

namespace Drupal\rating_app\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\rating_app\LikeDislikeInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the like dislike entity class.
 *
 * @ContentEntityType(
 *   id = "like_dislike",
 *   label = @Translation("like dislike"),
 *   label_collection = @Translation("like dislikes"),
 *   label_singular = @Translation("like dislike"),
 *   label_plural = @Translation("like dislikes"),
 *   label_count = @PluralTranslation(
 *     singular = "@count like dislikes",
 *     plural = "@count like dislikes",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\rating_app\LikeDislikeListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\rating_app\Form\LikeDislikeForm",
 *       "edit" = "Drupal\rating_app\Form\LikeDislikeForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "like_dislike",
 *   admin_permission = "administer like dislike",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/like-dislike",
 *     "add-form" = "/like-dislike/add",
 *     "canonical" = "/like-dislike/{like_dislike}",
 *     "edit-form" = "/like-dislike/{like_dislike}/edit",
 *     "delete-form" = "/like-dislike/{like_dislike}/delete",
 *   },
 *   field_ui_base_route = "entity.like_dislike.settings",
 * )
 */
class LikeDislike extends ContentEntityBase implements LikeDislikeInterface {
  
  use EntityChangedTrait;
  use EntityOwnerTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    $fields['label'] = BaseFieldDefinition::create('string')->setLabel(t('Label'))->setRequired(TRUE)->setSetting('max_length', 255)->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -5
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'string',
      'weight' => -5
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['status'] = BaseFieldDefinition::create('boolean')->setLabel(t('Status'))->setDefaultValue(TRUE)->setSetting('on_label', 'Enabled')->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'settings' => [
        'display_label' => FALSE
      ],
      'weight' => 0
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'type' => 'boolean',
      'label' => 'above',
      'weight' => 0,
      'settings' => [
        'format' => 'enabled-disabled'
      ]
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['like_dislike'] = BaseFieldDefinition::create('list_integer')->setLabel(" Like or dislike ")->setDisplayOptions('form', [
      'type' => 'options_buttons',
      'weight' => 5
    ])->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', true)->setSettings([
      'allowed_values' => [
        0 => 'none',
        1 => "Like",
        -1 => "Dislike"
      ]
    ])->setRequired(true)->setDefaultValue('tache');
    
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Author'))->setSetting('target_type', 'user')->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => 60,
        'placeholder' => ''
      ],
      'weight' => 15
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'author',
      'weight' => 15
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Authored on'))->setDescription(t('The time that the like dislike was created.'))->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'timestamp',
      'weight' => 20
    ])->setDisplayConfigurable('form', TRUE)->setDisplayOptions('form', [
      'type' => 'datetime_timestamp',
      'weight' => 20
    ])->setDisplayConfigurable('view', TRUE);
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the like dislike was last edited.'));
    
    return $fields;
  }
  
}
