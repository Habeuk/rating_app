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
      'foo' => 'bar'
    ] + parent::defaultSettings();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $element['foo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Foo'),
      '#default_value' => $settings['foo']
    ];
    return $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary[] = $this->t('Foo: @foo', [
      '@foo' => $settings['foo']
    ]);
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
      $id = Html::getUniqueId('rating-app-reviews');
      $element[] = [
        // add additionnal information
        '#comment_type' => $comment_type,
        '#comment_display_mode' => $this->getFieldSetting('default_mode'),
        'comments' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Loading ...'),
          '#attributes' => [
            'id' => $id,
            'class' => [
              'rating-app-start'
            ]
          ]
        ],
        'comment_form' => NULL
      ];
      // dump($items->getName());
      $element['#attached']['drupalSettings']['rating_app'] = [
        'start' => [
          'field_name' => $items->getName(),
          'comment_type' => $comment_type,
          'entity_id' => $items->getEntity()->id(),
          'entity_type_id' => $items->getEntity()->getEntityTypeId(),
          'url_get_start' => '/' . $urlStart->getInternalPath(),
          'id' => $id
        ] + $this->getSettings()
      ];
      $element['#attached']['library'][] = 'rating_app/reviews_start';
    }
    
    return $element;
  }
  
}
