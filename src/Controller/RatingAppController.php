<?php

namespace Drupal\rating_app\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for rating_app routes.
 */
class RatingAppController extends ControllerBase {
  
  /**
   * Builds the response.
   */
  public function build() {
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!')
    ];
    $comment = $this->entityTypeManager()->getStorage('comment')->load(1);
    dump($comment->toArray());
    return $build;
  }
  
}
