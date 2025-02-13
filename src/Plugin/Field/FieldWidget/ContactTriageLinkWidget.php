<?php

namespace Drupal\bhcc_contact_triage\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * A widget bar.
 *
 * @FieldWidget(
 *   id = "bhcc_contact_triage_link_widget",
 *   label = @Translation("Contact triage link"),
 *   field_types = {
 *     "contact_triage_link"
 *   }
 * )
 */
class ContactTriageLinkWidget extends WidgetBase implements WidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Get Delta for this specific entry.
    $values = $items[$delta]->getValue();

    $element['linkURL'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link URL'),
      '#default_value' => $values['linkURL'] ?? '',
      '#description' => $this->t('For internal links enter the path e.g. /housing or /node/2036. For external links enter the full URL including https://'),
    ];

    $element['linkText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Text'),
      '#default_value' => $values['linkText'] ?? '',
    ];

    return $element;
  }

}
