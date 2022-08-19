<?php

namespace Drupal\bhcc_contact_triage\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Contact triage link' formatter.
 *
 * @FieldFormatter(
 *   id = "bhcc_contact_triage_link_formatter",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "contact_triage_link"
 *   }
 * )
 */
class ContactTriageLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];

    if (!$items->isEmpty()) {
      foreach ($items as $delta => $item) {

        $linkURL = $item->linkURL;
        $linkText = $item->linkText;

        $elements[$delta] = array(
          '#markup' => $linkURL . ' ' . $linkText
        );
      }  
    }
    
    return $elements;
  }
}
