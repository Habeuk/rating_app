<?php

namespace Drupal\rating_app\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'rating_app_reviews_resume_default' formatter.
 *
 * @FieldFormatter(
 *   id = "rating_app_reviews_resume_default",
 *   label = @Translation("Default"),
 *   field_types = {"rating_app_reviews_resume"}
 * )
 */
class ReviewsResumeDefaultFormatter extends FormatterBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'foo' => 'bar',
      'comment_type' => 'comment'
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
    $entity = $items->getEntity();
    $element = [];
    if (!$entity->isNew()) {
      $urlGetReviews = Url::fromRoute('rating_app.get_reviews', [
        'entity_type_id' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id()
      ]);
      
      $element[] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'rating-app-reviews',
          'data-entity-id' => $items->getEntity()->id(),
          'data-entity-type-id' => $items->getEntity()->getEntityTypeId(),
          'data-url-get-reviews' => "/" . $urlGetReviews->getInternalPath(),
          'data-comment_type' => $this->getSetting('comment_type')
        ]
      ];
      $element['#attached']['library'][] = 'rating_app/reviews_resume';
    }
    foreach ($items as $delta => $item) {
      if ($item->value_1) {
        $element[$delta]['value_1'] = [
          '#type' => 'item',
          '#title' => $this->t('Value 1'),
          '#markup' => $item->value_1
        ];
      }
    }
    return $element;
  }
  
}
