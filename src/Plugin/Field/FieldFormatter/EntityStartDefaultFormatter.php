<?php

namespace Drupal\rating_app\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'rating_app_entity_start_default' formatter.
 *
 * @FieldFormatter(
 *   id = "rating_app_entity_start_default",
 *   label = @Translation("Rating api star"),
 *   field_types = {
 *     "comment"
 *   }
 * )
 */
class EntityStartDefaultFormatter extends FormatterBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'class_container' => '',
      'size' => '',
      'options_size' => [
        '' => 'Normal',
        'comment-stars--small' => 'Comment-stars small',
        'comment-stars--big' => 'Comment-stars big'
      ]
    ] + parent::defaultSettings();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $element['class_container'] = [
      '#type' => 'textfield',
      '#title' => $this->t('class_container'),
      '#default_value' => $settings['class_container']
    ];
    $element['size'] = [
      '#type' => 'select',
      '#title' => $this->t('size'),
      '#default_value' => $settings['size'],
      '#options' => $this->getSetting('options_size')
    ];
    return $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    return $summary;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // dump($langcode);
    $entity = $items->getEntity();
    $element = [];
    if (!$entity->isNew()) {
      $urlStart = URL::fromRoute('rating_app.get_start', [
        'entity_type_id' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id(),
        'field_name' => $items->getName()
      ]);
      $comment_type = $this->getFieldSetting('comment_type');
      $class_container = $this->getSetting('class_container');
      $size = $this->getSetting('size');
      
      $element[] = [
        // add additionnal information
        '#comment_type' => $comment_type,
        '#comment_display_mode' => $this->getFieldSetting('default_mode'),
        'comments' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Loading ...'),
          '#attributes' => [
            'data_entity_id' => $items->getEntity()->id(),
            'data_url_get_start' => '/' . $urlStart->getInternalPath(),
            'class' => [
              'rating-app-start',
              $class_container,
              $size
            ]
          ]
        ],
        'comment_form' => NULL
      ];
      $element['#attached']['drupalSettings']['rating_app'] = [
        'start' => [
          'field_name' => $items->getName(),
          'comment_type' => $comment_type,
          // 'entity_id' => $items->getEntity()->id(),
          'entity_type_id' => $items->getEntity()->getEntityTypeId()
          // 'url_get_start' => '/' . $urlStart->getInternalPath()
        ] + $this->getSettings()
      ];
      $element['#attached']['library'][] = 'rating_app/reviews_start';
    }
    
    return $element;
  }
  
}
