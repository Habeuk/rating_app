<?php

namespace Drupal\rating_app\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rating_app\Services\ManagerRatingApp;
use Stephane888\DrupalUtility\HttpResponse;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\Debug\ExceptionDebug;
use Stephane888\Debug\ExceptionExtractMessage;
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
  public function getReviews(Request $Request, $entity_type_id, $entity_id, $field_name) {
    $limit = $Request->query->get('limit', 10);
    $page = $Request->query->get('page', 1);
    $note = $Request->query->get('note');
    $datas = $this->ManagerRatingApp->getAppReviews($entity_type_id, $entity_id, $field_name, $limit, $page, $note);
    return HttpResponse::response($datas);
  }
  
  public function getStart(Request $Request, $entity_type_id, $entity_id, $field_name) {
    $datas = $this->ManagerRatingApp->getstartAverage($entity_type_id, $entity_id, $field_name);
    return HttpResponse::response($datas);
  }
  
  /**
   *
   * @param Request $Request
   */
  public function SaveReview(Request $Request, $entity_type_id, $entity_id) {
    try {
      $datas = Json::decode($Request->getContent());
      if (!empty($datas['form']['start']) && !empty($datas['form']['comment']) && !empty($datas['form']['comment_type']) && isset($datas['form']['titre'])) {
        $uid = \Drupal::currentUser()->id();
        $comment = [
          'entity_type' => $entity_type_id,
          'entity_id' => $entity_id,
          'subject' => $datas['form']['titre'],
          'uid' => $uid,
          'comment_body' => $datas['form']['comment'],
          'comment_type' => $datas['form']['comment_type'],
          'field_name' => $datas['form']['field_name']
        ];
        $commentId = $this->ManagerRatingApp->addComment($comment);
        
        $start = [
          'value' => $datas['form']['start'],
          'comment_type' => $entity_type_id,
          'comment_id' => $entity_id,
          'rating_app_comment_id' => $commentId,
          'value_type' => 'points',
          'user_id' => $uid
        ];
        $result['note'] = $this->ManagerRatingApp->addNote($start);
        return HttpResponse::response($result);
      }
      throw ExceptionDebug::exception("Some key not exist");
    }
    catch (\Exception $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 400, $e);
    }
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
