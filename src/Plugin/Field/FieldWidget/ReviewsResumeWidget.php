<?php

namespace Drupal\rating_app\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Defines the 'rating_app_reviews_resume' field widget.
 *
 * @FieldWidget(
 *   id = "rating_app_reviews_resume",
 *   label = @Translation("Reviews resume"),
 *   field_types = {"rating_app_reviews_resume"},
 * )
 */
class ReviewsResumeWidget extends WidgetBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = [
      '#type' => 'hiden',
      '#title' => $this->t('Value 1'),
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => 20
    ];
    
    $element['#theme_wrappers'] = [
      'container',
      'form_element'
    ];
    $element['#attributes']['class'][] = 'container-inline';
    $element['#attributes']['class'][] = 'rating-app-reviews-resume-elements';
    $element['#attached']['library'][] = 'rating_app/rating_app_reviews_resume';
    
    return $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return isset($violation->arrayPropertyPath[0]) ? $element[$violation->arrayPropertyPath[0]] : $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if ($value['value'] === '') {
        $values[$delta]['value'] = NULL;
      }
    }
    return $values;
  }
  
}
