<?php

namespace Drupal\rating_app\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rating_app\Services\ManagerRatingApp;
use Stephane888\DrupalUtility\HttpResponse;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\Debug\ExceptionDebug;
use Drupal\Component\Serialization\Json;

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
  
  public function LikeDislikeReview(Request $Request, $entity_type_id, $entity_id) {
    $datas = [];
    try {
      $uid = \Drupal::currentUser()->id();
      $datas = Json::decode($Request->getContent());
      if (isset($datas['value'])) {
        $datas['value'] = (int) $datas['value'];
        if ($datas['value'] === 1 || $datas['value'] === -1) {
          $datas['comment_type'] = $entity_type_id;
          $datas['comment_id'] = $entity_id;
          $datas['value_type'] = 'points';
          $datas['user_id'] = \Drupal::currentUser()->id();
          return HttpResponse::response($this->ManagerRatingApp->LikeDislike($datas));
        }
      }
      if (!$uid) {
        throw ExceptionDebug::exception(" User must connecté to like or dislike ");
      }
      throw ExceptionDebug::exception("key not exist");
    }
    catch (\Error $e) {
      return HttpResponse::response($e->getMessage(), 400);
    }
  }
  
}
