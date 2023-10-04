<?php

namespace Drupal\rating_app\Services;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Gere les avis
 *
 * @author stephane
 *        
 */
class ManagerRatingApp {
  
  /**
   *
   * @var string
   */
  protected $entity_type_id;
  
  /**
   *
   * @var string
   */
  protected $entity_id;
  
  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  
  /**
   *
   * @var EntityTypeManager
   */
  protected $EntityTypeManager;
  
  function __construct(MessengerInterface $messenger, EntityTypeManager $EntityTypeManager) {
    $this->messenger = $messenger;
    $this->EntityTypeManager = $EntityTypeManager;
  }
  
  /**
   *
   * @param string $entity_type_id
   * @param mixed $entity_id
   */
  function getAppReviews($entity_type_id, $entity_id) {
    $this->entity_type_id = $entity_type_id;
    $this->entity_id = $entity_id;
    return [
      'reviews' => $this->getReviews(),
      'summary' => $this->getSummary(),
      'configs' => [
        'review' => [
          'status_user_display' => true,
          'status_user_text' => 'Acheteur -',
          'status_user_badge' => true
        ],
        ''
      ]
    ];
  }
  
  /**
   *
   * @return string[]|number[]
   */
  protected function getReviews() {
    $query = $this->EntityTypeManager->getStorage('comment')->getQuery();
    $query->condition('entity_type', $this->entity_type_id);
    $query->condition('entity_id', $this->entity_id);
    $query->accessCheck(true);
    $query->sort('created', 'DESC');
    $ids = $query->execute();
    $comments = [];
    if ($ids) {
      $entities = $this->EntityTypeManager->getStorage('comment')->loadMultiple($ids);
      foreach ($entities as $entity) {
        /**
         *
         * @var \Drupal\comment\Entity\Comment $entity
         */
        $comments[] = [
          'id' => $entity->id(),
          'product_handler' => '', // ??
          'name' => $entity->getAuthorName(),
          'title' => $entity->getSubject(),
          'email' => $entity->getAuthorEmail(), // ?
          'description' => $entity->get('comment_body')->value,
          'reponse' => '', // doit etre un array.
          'note' => 4.5,
          'likes' => 1,
          'dislikes' => 0,
          'surname' => '',
          'created_at' => $entity->getCreatedTime(),
          'status_user_text' => 'Acheteur vérifié...',
          'status_user_badge' => true
        ];
      }
    }
    return $comments;
  }
  
  /**
   *
   * @return number[]
   */
  protected function getSummary() {
    return [
      'note_5' => 5,
      'note_4' => 4,
      'note_3' => 0,
      'note_2' => 1,
      'note_1' => 0
    ];
  }
  
}