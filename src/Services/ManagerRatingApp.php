<?php

namespace Drupal\rating_app\Services;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\votingapi\Entity\Vote;
use Drupal\Core\Database\Connection;

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;
  /**
   *
   * @var string
   */
  protected static $like_comment = 'like_comment';
  /**
   *
   * @var string
   */
  protected static $dislike_comment = 'dislike_comment';
  
  /**
   *
   * @var EntityTypeManager
   */
  protected $EntityTypeManager;
  
  function __construct(MessengerInterface $messenger, EntityTypeManager $EntityTypeManager, Connection $database) {
    $this->messenger = $messenger;
    $this->EntityTypeManager = $EntityTypeManager;
    $this->database = $database;
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
          'likes' => $this->getLikesDislikes(self::$like_comment, $entity->bundle(), $entity->id()),
          'dislikes' => $this->getLikesDislikes(self::$dislike_comment, $entity->bundle(), $entity->id()),
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
  
  /**
   * Permet de sauvegarder le nombre de
   */
  public function LikeDislike(array $data) {
    $type = self::$like_comment;
    if ($data['value'] === -1)
      $type = self::$dislike_comment;
    $hasVote = $this->userHasLikeDislike($data);
    if ($hasVote) {
      // on supprime l'ancien vote de l'utilisateur.
      if ($hasVote->get('type')->target_id !== $type) {
        $hasVote->delete();
      }
      else {
        return $hasVote->id();
      }
    }
    $vote = Vote::create([
      'type' => $type,
      'entity_type' => $data['comment_type'],
      'entity_id' => $data['comment_id'],
      'value' => $data['value'],
      'value_type' => $data['value_type'],
      'user_id' => $data['user_id'],
      'vote_source' => \Drupal\votingapi\Entity\Vote::getCurrentIp()
    ]);
    $vote->save();
    return $vote->id();
  }
  
  /**
   *
   * @param string $voteType
   * @param string $comment_type
   * @param string $entity_id
   * @return array
   */
  protected function getLikesDislikes($voteType, $comment_type, $entity_id) {
    $results = [];
    $result = $this->database->select('votingapi_result', 'v')->fields('v', [
      'type',
      'function',
      'value'
    ])->condition('type', $voteType)->condition('entity_type', $comment_type)->condition('entity_id', $entity_id)->execute();
    while ($row = $result->fetchAssoc()) {
      $results[$row['type']][$row['function']] = $row['value'];
    }
    if ($results) {
      return $results[$voteType]['vote_sum'];
    }
    return 0;
  }
  
  /**
   * Check if current user has voted.
   *
   * @return Vote|Null
   */
  protected function userHasLikeDislike(array $data) {
    $query = $this->EntityTypeManager->getStorage('vote')->getQuery()->accessCheck(TRUE);
    $or = $query->orConditionGroup();
    $or->condition('type', self::$like_comment);
    $or->condition('type', self::$dislike_comment);
    $query->condition($or);
    $query->condition('entity_type', $data['comment_type']);
    $query->condition('entity_id', $data['comment_id']);
    $query->condition('user_id', $data['user_id']);
    $ids = $query->execute();
    if ($ids) {
      $id = reset($ids);
      return $this->EntityTypeManager->getStorage('vote')->load($id);
    }
    return null;
  }
  
}