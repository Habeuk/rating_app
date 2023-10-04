<?php

namespace Drupal\rating_app\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rating_app\Services\ManagerRatingApp;
use Stephane888\DrupalUtility\HttpResponse;

/**
 * Returns responses for rating_app routes.
 */
class RatingAppController extends ControllerBase {
  /**
   *
   * @var ManagerRatingApp
   */
  protected $ManagerRatingApp;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('rating_app.manager'));
  }
  
  function __construct(ManagerRatingApp $ManagerRatingApp) {
    $this->ManagerRatingApp = $ManagerRatingApp;
  }
  
  /**
   * Builds the response.
   */
  public function getReviews($entity_type_id, $entity_id) {
    $datas = $this->ManagerRatingApp->getAppReviews($entity_type_id, $entity_id);
    return HttpResponse::response($datas);
  }
  
  public function LikeDislikeReview($entity_type_id, $entity_id, $value) {
    $datas = [];
    return HttpResponse::response($datas);
  }
  
}
