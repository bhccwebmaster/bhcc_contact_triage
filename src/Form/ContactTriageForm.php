<?php

namespace Drupal\bhcc_contact_triage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Allows users to search for jobs through the Tribepad API.
 */
class ContactTriageForm extends FormBase {

  /**
   * Get the form ID of the contact triage.
   *
   * @inheritdoc
   */
  public function getFormId() {
    return 'bhcc_contact_triage_form';
  }

  /**
   * Function to build the contact triage form elements.
   *
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, $triageOptions = NULL, $triageQuestion = NULL, $triageFormat = 'radio') {

    $form['#attributes']['class'][] = 'bhcc-contact-form';

    $optionArray = [];

    foreach ($triageOptions as $key => $val) {
      $optionArray[$val['optURL'] . '~' . $key] = $val['optText'];
    }

    $form['link_options'] = [
      '#type' => 'value',
      '#value' => $optionArray,
    ];

    if ($triageFormat == 'radio') {
      $form['links_radios'] = [
        '#title' => $this->t('%triageQuestion', ['%triageQuestion' => $triageQuestion]),
        '#type' => 'radios',
        '#options' => $form['link_options']['#value'],
      ];
    }

    if ($triageFormat == 'select') {
      $form['links_radios'] = [
        '#title' => $this->t('%triageQuestion', ['%triageQuestion' => $triageQuestion]),
        '#type' => 'select',
        '#options' => $form['link_options']['#value'],
      ];
    }

    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'form-actions',
        ],
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Next',
    ];

    return $form;

  }

  /**
   * Function to validate the field elements.
   *
   * (@inheritdoc)
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $formValue = $form_state->getValue('links_radios');
    if ($formValue == NULL) {
      $form_state->setErrorByName('links_radios', $this->t('Please select one of the options'));
      return;
    }
  }

  /**
   * Function to submit the contact triage form.
   *
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $formValue = $form_state->getValue('links_radios');

    $trimmedURL = substr($formValue, 0, strpos($formValue, "~"));

    $response = new TrustedRedirectResponse($trimmedURL);
    $form_state->setResponse($response);

  }

}
