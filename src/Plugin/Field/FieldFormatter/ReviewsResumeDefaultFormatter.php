<?php

namespace Drupal\rating_app\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\comment\Plugin\Field\FieldFormatter\CommentDefaultFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'rating_app_reviews_resume_default' formatter.
 *
 * @FieldFormatter(
 *   id = "rating_app_reviews_resume_default",
 *   label = @Translation("Rating api reviews"),
 *   field_types = {
 *     "comment"
 *   }
 * )
 */
class ReviewsResumeDefaultFormatter extends CommentDefaultFormatter {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'comments_per_pages' => 10
    ] + parent::defaultSettings();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    //
    return $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    //
    return $summary;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // dump($items);
    $entity = $items->getEntity();
    $element = [];
    if (!$entity->isNew()) {
      $urlGetReviews = Url::fromRoute('rating_app.get_reviews', [
        'entity_type_id' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id(),
        'field_name' => $items->getName()
      ]);
      $urlAddComment = Url::fromRoute('rating_app.add_comment', [
        'entity_type_id' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id()
      ]);
      $comment_type = $this->getFieldSetting('comment_type');
      $id = Html::getUniqueId('rating-app-start');
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
            'data-entity-id' => $items->getEntity()->id(),
            'data-entity-type-id' => $items->getEntity()->getEntityTypeId(),
            'data-url-get-reviews' => "/" . $urlGetReviews->getInternalPath(),
            'data-add_comment' => "/" . $urlAddComment->getInternalPath()
          ]
        ],
        'comment_form' => NULL
      ];
      // dump($items->getName());
      $element['#attached']['drupalSettings']['rating_app'] = [
        'review' => [
          'field_name' => $items->getName(),
          'comment_type' => $comment_type,
          'entity_id' => $items->getEntity()->id(),
          'entity_type_id' => $items->getEntity()->getEntityTypeId(),
          'id' => $id
        ] + $this->getSettings()
      ];
      $element['#attached']['library'][] = 'rating_app/reviews_resume';
    }
    
    return $element;
  }
  
}
