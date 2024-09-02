<?php

namespace Drupal\rating_app;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a like dislike entity type.
 */
interface LikeDislikeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
