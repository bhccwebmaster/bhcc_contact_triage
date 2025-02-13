<?php

namespace Drupal\bhcc_contact_triage\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of Contact triage link.
 *
 * @FieldType(
 *   id = "contact_triage_link",
 *   label = @Translation("Contact triage"),
 *   default_formatter = "bhcc_contact_triage_link_formatter",
 *   default_widget = "bhcc_contact_triage_link_widget",
 * )
 */
class ContactTriageLinkItem extends FieldItemBase implements FieldItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'linkURL' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
        'linkText' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('linkURL')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['linkURL'] = DataDefinition::create('string')->setLabel(t('Link URL'));
    $properties['linkText'] = DataDefinition::create('string')->setLabel(t('Link Text'));

    return $properties;
  }

}
