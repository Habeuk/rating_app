<?php

namespace Drupal\rating_app\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the like dislike entity edit forms.
 */
class LikeDislikeForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New like dislike %label has been created.', $message_arguments));
        $this->logger('rating_app')->notice('Created new like dislike %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The like dislike %label has been updated.', $message_arguments));
        $this->logger('rating_app')->notice('Updated like dislike %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.like_dislike.canonical', ['like_dislike' => $entity->id()]);

    return $result;
  }

}
