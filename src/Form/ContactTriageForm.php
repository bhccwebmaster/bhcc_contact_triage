<?php

namespace Drupal\bhcc_contact_triage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a contact triage form based on provided options.
 *
 * This will usually be from a 'contact triage' node.
 */
class ContactTriageForm extends FormBase implements ContainerInjectionInterface {

  /**
   * Messenger Service.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface
   *   Messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bhcc_contact_triage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $triageOptions = NULL, $triageQuestion = NULL, $triageFormat = 'radio') {

    $form['#attributes']['class'][] = 'bhcc-contact-form';

    $optionArray = [];

    foreach ($triageOptions as $key => $val) {
      $optionArray[$val['optURL'] . '~' . $key] = $val['optText'];
    }

    if ($triageFormat == 'radio') {
      $form['links_radios'] = [
        '#title' => $triageQuestion,
        '#type' => 'radios',
        '#options' => $optionArray,
      ];
    }

    if ($triageFormat == 'select') {
      $form['links_radios'] = [
        '#title' => $triageQuestion,
        '#type' => 'select',
        '#options' => $optionArray,
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  
    $formValue = $form_state->getValue('links_radios');
    if ($formValue == NULL) {

      // This deletes all errors, but we only want to delete the question.
      // @todo: find out how to delete specific duplicate error.
      $this->messenger->deleteByType('error');

      // Set the error.
      $form_state->setErrorByName('links_radios', $this->t('Please select one of the options'));
      $form_errors = $form_state->getErrors();

      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $formValue = $form_state->getValue('links_radios');

    $trimmedURL = substr($formValue, 0, strpos($formValue, "~"));

    $response = new TrustedRedirectResponse($trimmedURL);
    $form_state->setResponse($response);

  }

}
