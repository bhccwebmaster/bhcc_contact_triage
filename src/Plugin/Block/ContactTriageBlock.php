<?php

namespace Drupal\bhcc_contact_triage\Plugin\Block;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
  * Provides a search block for Tribepad
  * @Block (
  *   id = "contact_triage_block",
  *   admin_label = "Contact triage block"
  * )
  */
class ContactTriageBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var bool|\Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return \Drupal\bhcc_contact_triage\Plugin\Block\ContactTriageBlock|\Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * MapsBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\bhcc_helper\CurrentPage $currentPage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $currentPage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Get the current node.
    // @todo Move this out of the contrcutor.
    if ($currentPage->getParameters()->has('node') && $node = $currentPage->getParameter('node')) {

      // Make sure this is a node object, otherwise load it.
      // Fix bug DRUP-1237.
      if (!$node instanceof NodeInterface) {
        $node = Node::load((int) $node);
      }
      $this->node = $node;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];

    $links = [];

    $triageOptions = $this->node->get('field_contact_triage_link');

    $triageQuestion = $this->node->get('field_contact_triage_question');

    $triageFormat = $this->node->get('field_form_format');

    $question = '';

    $format = '';

    if ($triageQuestion) {
      $question = $triageQuestion->first()->getValue()['value'];
    }

    if ($triageFormat && $triageFormat->first()) {
      $format = $triageFormat->first()->getValue()['value'];
    } else {
      $format = 'radio';
    }

    if (!$triageOptions->isEmpty()) {
      foreach ($triageOptions as $delta => $item) {
        $links[$delta] = ['optText' => $item->linkText, 'optURL' => $item->linkURL];
      }
    }

    $build[] = \Drupal::formBuilder()->getForm('Drupal\bhcc_contact_triage\Form\ContactTriageForm', $links, $question, $format);

    \Drupal::service('page_cache_kill_switch')->trigger();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node:' . $this->node->id()]);
  }

  
    
  

}
