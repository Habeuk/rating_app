<?php

namespace Drupal\rating_app\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

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
    $element = [];
    
    foreach ($items as $delta => $item) {
      
      if ($item->value) {
        $element[$delta]['value'] = [
          '#type' => 'item',
          '#title' => $this->t('Value'),
          '#markup' => $item->value
        ];
      }
    }
    
    return $element;
  }
  
}
