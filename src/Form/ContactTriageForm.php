<?php

/**
 * @file
 * Contains \Drupal\bhcc_contact_triage\Form\SearchForm
 */

namespace Drupal\bhcc_contact_triage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\HTML;
use Drupal\node\Entity\Node;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Allows users to search for jobs through the Tribepad API
 */

class ContactTriageForm extends FormBase {
	
  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'bhcc_contact_triage_form';
  }
  
  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, $triageOptions = NULL, $triageQuestion = NULL, $triageFormat = 'radio') {
 
    $form['#attributes']['class'][] = 'bhcc-contact-form';

    $optionArray = [];

    foreach($triageOptions as $key => $val) {
      $optionArray[$val['optURL'] . '~' . $key] = $val['optText'];
    }


    $form['link_options'] = Array(
      '#type' => 'value',
      '#value' => $optionArray,
    );

    if($triageFormat == 'radio') {
      $form['links_radios'] = Array(
        '#title' => t($triageQuestion),
        '#type' => 'radios',
        '#options' => $form['link_options']['#value']
      );
    }

    if($triageFormat == 'select') {
      $form['links_radios'] = Array(
        '#title' => t($triageQuestion),
        '#type' => 'select',
        '#options' => $form['link_options']['#value']
      );      
    }

    
    $form['submit'] = Array (
      '#type' => 'submit',
      '#value' => 'Next',
      '#attributes' => ['class' => ['cta-button cta-green webform-button--submit']],
    );

    return $form;

  }

  /**
   * (@inheritdoc)
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $formValue = $form_state->getValue('links_radios');
    if ($formValue == NULL) {
      $form_state->setErrorByName ('links_radios', t('Please select one of the options'));
      return;		
    }
  }
    
  
  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $formValue = $form_state->getValue('links_radios');

    $trimmedURL = substr($formValue, 0, strpos($formValue, "~"));

    $response = new TrustedRedirectResponse($trimmedURL);
    $form_state->setResponse($response);

  }
}