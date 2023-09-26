<?php

namespace Drupal\ratting_app\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for ratting_app routes.
 */
class RattingAppController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
