<?php

namespace Drupal\rating_app\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'rating_app_entity_start' field type.
 *
 * @FieldType(
 *   id = "rating_app_entity_start",
 *   label = @Translation("entity start"),
 *   category = @Translation("Reviews"),
 *   default_widget = "rating_app_entity_start",
 *   default_formatter = "rating_app_entity_start_default"
 * )
 */
class EntityStartItem extends FieldItemBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->value_1 !== NULL) {
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value_1'] = DataDefinition::create('string')->setLabel(t('Value 1'));
    
    return $properties;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    
    // @todo Add more constraints here.
    return $constraints;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $columns = [
      'value_1' => [
        'type' => 'varchar',
        'length' => 255
      ]
    ];
    
    $schema = [
      'columns' => $columns
      // @DCG Add indexes here if necessary.
    ];
    
    return $schema;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    
    $values['value_1'] = $random->word(mt_rand(1, 255));
    
    return $values;
  }
  
}
