<?php

namespace Drupal\rating_app\Services;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\votingapi\Entity\Vote;
use Drupal\Core\Database\Connection;
use Drupal\comment\Entity\Comment;
use PDO;

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
   *
   * @var string
   */
  protected $field_comment_name;
  
  /**
   *
   * @var integer
   */
  protected $limit = 10;
  
  /**
   *
   * @var integer
   */
  protected $page = 0;
  
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
  protected $note;
  /**
   *
   * @var string
   */
  protected static $vote_type__like_comment = 'rating_app__like_comment';
  /**
   *
   * @var string
   */
  protected static $vote_type__dislike_comment = 'rating_app__dislike_comment';
  /**
   * La valeur reelle varie de de 1 à 5.
   * ( example : note_1 ).
   *
   * @var string
   */
  protected static $vote_type__comment_note = 'rating_app__note_';
  
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
  function getAppReviews($entity_type_id, $entity_id, $field_name, $limit, $page, $note) {
    $this->entity_type_id = $entity_type_id;
    $this->entity_id = $entity_id;
    $this->field_comment_name = $field_name;
    $this->limit = $limit;
    $this->page = $page - 1;
    $this->note = $note;
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
   * @param string $entity_type_id
   * @param mixed $entity_id
   */
  function getstartAverage($entity_type_id, $entity_id, $field_name) {
    $this->entity_type_id = $entity_type_id;
    $this->entity_id = $entity_id;
    $this->field_comment_name = $field_name;
    //
    $query = $this->database->select('votingapi_vote', 'v')->fields('v', [
      'value'
    ])->condition('entity_type', $this->entity_type_id)->condition('entity_id', $this->entity_id);
    $or = $query->orConditionGroup();
    $or->condition('type', self::$vote_type__comment_note . '1');
    $or->condition('type', self::$vote_type__comment_note . '2');
    $or->condition('type', self::$vote_type__comment_note . '3');
    $or->condition('type', self::$vote_type__comment_note . '4');
    $or->condition('type', self::$vote_type__comment_note . '5');
    $query->condition($or);
    $result = $query->execute();
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    $nbre = count($rows);
    $val = 0;
    if ($rows) {
      foreach ($rows as $row) {
        $val += $row['value'];
      }
    }
    return [
      'count' => $nbre,
      'percent' => $nbre ? $val / $nbre * 20 : $nbre // pour avoir un resulta
                                                     // sur 100.
    ];
  }
  
  /**
   *
   * @return string[]|number[]
   */
  protected function getReviews() {
    $start = $this->page * $this->limit;
    $ids = [];
    if ($this->note) {
      $query = $this->database->select('comment_field_data', 'v')->fields('v', [
        'cid'
      ]);
      $query->condition('v.entity_type', $this->entity_type_id);
      $query->condition('v.entity_id', $this->entity_id);
      $query->orderBy('v.created', 'DESC');
      $query->range($start, $this->limit);
      //
      $query->addJoin('INNER', 'votingapi_vote', 'vtv', 'vtv.rating_app_comment_id=v.cid');
      $query->condition('vtv.type', self::$vote_type__comment_note . $this->note);
      $results = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $row) {
        $ids[$row['cid']] = $row['cid'];
      }
    }
    else {
      $query = $this->EntityTypeManager->getStorage('comment')->getQuery();
      $query->condition('entity_type', $this->entity_type_id);
      $query->condition('entity_id', $this->entity_id);
      $query->accessCheck(true);
      $query->sort('created', 'DESC');
      $query->range($start, $this->limit);
      $ids = $query->execute();
    }
    
    $comments = [];
    if ($ids) {
      $entities = $this->EntityTypeManager->getStorage('comment')->loadMultiple($ids);
      foreach ($entities as $entity) {
        $id = $entity->id();
        $bundle = $entity->bundle();
        /**
         *
         * @var \Drupal\comment\Entity\Comment $entity
         */
        $comments[] = [
          'id' => $id,
          'product_handler' => '', // ??
          'name' => $entity->getAuthorName(),
          'title' => $entity->getSubject(),
          'email' => $entity->getAuthorEmail(), // ?
          'description' => $entity->get('comment_body')->value,
          'reponse' => '', // doit etre un array.
          'note' => $this->getNotes($id),
          'likes' => $this->getLikesDislikes(self::$vote_type__like_comment, $bundle, $id),
          'dislikes' => $this->getLikesDislikes(self::$vote_type__dislike_comment, $bundle, $id),
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
    $entity = $this->EntityTypeManager->getStorage($this->entity_type_id)->load($this->entity_id);
    
    return [
      'note_5' => $this->getNotesByType(5),
      'note_4' => $this->getNotesByType(4),
      'note_3' => $this->getNotesByType(3),
      'note_2' => $this->getNotesByType(2),
      'note_1' => $this->getNotesByType(1)
    ];
  }
  
  public function getNotesByType($note_type = 1) {
    $type = self::$vote_type__comment_note . $note_type;
    $results = [];
    $result = $this->database->select('votingapi_result', 'v')->fields('v', [
      'type',
      'function',
      'value'
    ])->condition('entity_type', $this->entity_type_id)->condition('entity_id', $this->entity_id)->condition('type', $type)->execute();
    while ($row = $result->fetchAssoc()) {
      $results[$row['type']][$row['function']] = $row['value'];
    }
    if (!empty($results[$type])) {
      return $results[$type]['vote_count'];
    }
    return 0;
  }
  
  /**
   *
   * @param array $datas
   * @return integer
   */
  public function addComment(array $datas) {
    $comment = Comment::create($datas);
    $comment->save();
    return $comment->id();
  }
  
  public function addNote(array $data) {
    $vote_type_note = self::$vote_type__comment_note;
    $note = (int) $data['value'];
    $vote_type_note .= $note;
    $vote = Vote::create([
      'type' => $vote_type_note,
      'entity_type' => $data['comment_type'],
      'entity_id' => $data['comment_id'],
      'value' => $data['value'],
      'value_type' => $data['value_type'],
      'user_id' => $data['user_id'],
      'rating_app_comment_id' => $data['rating_app_comment_id'],
      'vote_source' => \Drupal\votingapi\Entity\Vote::getCurrentIp()
    ]);
    $vote->save();
    return $vote->id();
  }
  
  /**
   *
   * @param integer $commentId
   * @return number
   */
  public function getNotes($commentId) {
    $result = $this->database->select('votingapi_vote', 'v')->fields('v', [
      'value'
    ])->condition('entity_type', $this->entity_type_id)->condition('entity_id', $this->entity_id)->condition('rating_app_comment_id', $commentId)->execute();
    $row = $result->fetchAssoc();
    if (isset($row['value']))
      return (int) $row['value'];
    return 0;
  }
  
  /**
   * Permet de sauvegarder le nombre de
   */
  public function LikeDislike(array $data) {
    $type = self::$vote_type__like_comment;
    if ($data['value'] === -1)
      $type = self::$vote_type__dislike_comment;
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
    $or->condition('type', self::$vote_type__like_comment);
    $or->condition('type', self::$vote_type__dislike_comment);
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