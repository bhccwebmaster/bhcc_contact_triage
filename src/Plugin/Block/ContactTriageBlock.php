<?php

namespace Drupal\bhcc_contact_triage\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\core\Entity\EntityTypeManager;

/**
 * Provides a contact triage block.
 *
 * Options are set dynamically, usually by the 'contact_triage' node.
 *
 * @Block (
 *   id = "contact_triage_block",
 *   admin_label = "Contact triage block"
 * )
 */
class ContactTriageBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node.
   *
   * @var bool|\Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Form builder that will be used via Dependency Injection.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The kill switch that will be used via Dependency Injection.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Entity Type Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('form_builder'),
      $container->get('page_cache_kill_switch'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Contact triage block constructor.
   *
   * @param array $configuration
   *   The configuration to use.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current page.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupa\Core\PageCache\ResponsePolicy\Killswitch $killSwitch
   *   The page cache kill switch service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, RouteMatchInterface $route_match, FormBuilderInterface $form_builder, Killswitch $killSwitch, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->formBuilder = $form_builder;
    $this->killSwitch = $killSwitch;
    $this->entityTypeManager = $entity_type_manager;

    // Get the current node.
    // @todo Move this out of the contrcutor.
    if ($route_match->getParameters()->has('node') && $node = $route_match->getParameter('node')) {
      $node_storage = $this->entityTypeManager->getStorage('node');
      // Make sure this is a node object, otherwise load it.
      // Fix bug DRUP-1237.
      if (!$node instanceof NodeInterface) {
        $this->node = $node_storage->load((int) $node);
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
    }
    else {
      $format = 'radio';
    }

    if (!$triageOptions->isEmpty()) {
      foreach ($triageOptions as $delta => $item) {
        $links[$delta] =
        [
          'optText' => $item->linkText,
          'optURL' => $item->linkURL,
        ];
      }
    }

    $build[] = $this->formBuilder->getForm('Drupal\bhcc_contact_triage\Form\ContactTriageForm', $links, $question, $format);

    $this->killSwitch->trigger();

    // Add additional information.
    if ($this->node->hasField('bhcc_triage_additional_info')) {
      $aditional_info = $this->node->get('bhcc_triage_additional_info')->view();
      $aditional_info['#label_display'] = 'hidden';
      $build[] = $aditional_info;
    }

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
