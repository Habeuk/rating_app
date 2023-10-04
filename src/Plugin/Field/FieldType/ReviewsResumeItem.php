<?php

namespace Drupal\rating_app\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'rating_app_reviews_resume' field type.
 *
 * @FieldType(
 *   id = "rating_app_reviews_resume",
 *   label = @Translation("Reviews resume"),
 *   category = @Translation("Reviews"),
 *   default_widget = "rating_app_reviews_resume",
 *   default_formatter = "rating_app_reviews_resume_default"
 * )
 */
class ReviewsResumeItem extends FieldItemBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = [
      'bar' => 'example'
    ];
    return $settings + parent::defaultFieldSettings();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    
    $element['bar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Foo'),
      '#default_value' => $settings['bar']
    ];
    
    return $element;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->value !== NULL) {
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')->setLabel(t('Value'));
    
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
      'value' => [
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
    
    $values['value'] = $random->word(mt_rand(1, 255));
    
    return $values;
  }
  
}
