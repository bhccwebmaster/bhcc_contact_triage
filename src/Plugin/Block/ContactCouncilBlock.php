<?php

namespace Drupal\bhcc_contact_triage\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\core\Entity\EntityTypeManager;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a contact the council block.
 *
 * @Block (
 *   id = "contact_council_block",
 *   admin_label = "Contact the council triage block"
 * )
 */
class ContactCouncilBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Entity Type Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The kill switch that will be used via Dependency Injection.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

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
   * Contact Council block constructor.
   *
   * @param array $configuration
   *   The configuration to use.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
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
    $this->routeMatch = $route_match;

    // Get the current node.
    // @todo Move this out of the contrcutor.
    if ($route_match->getParameters()->has('node') && $node = $route_match->getParameter('node')) {

      // Make sure this is a node object, otherwise load it.
      // Fix bug DRUP-1237.
      if (!$node instanceof NodeInterface) {
        $this->node = $this->entityTypeManager->getStorage('node')->load((int) $node);
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

    $node_storage = $this->entityTypeManager->getStorage('node');
    $nodeRef = Settings::get('contact_triage_node', '');

    $otherNode = $this->node = $node_storage->load($nodeRef);

    if ($otherNode) {

      $triageOptions = $otherNode->field_contact_triage_link->getValue();

      $triageQuestion = $otherNode->field_contact_triage_question->getValue();

      $question = '';

      if ($triageQuestion) {
        $question = $triageQuestion[0]['value'];
      }

      if ($triageOptions) {
        foreach ($triageOptions as $delta => $item) {
          $links[$delta] =
          [
            'optText' => $item['linkText'],
            'optURL' => $item['linkURL'],
          ];
        }
      }

      $build[] = $this->formBuilder->getForm('Drupal\bhcc_contact_triage\Form\ContactTriageForm', $links, $question);

    }

    else {
      $build[] = [
        "#markup" => "<div class='alert alert-warning'>Contact form unavailable</div>",
      ];
    }

    $this->killSwitch->trigger();

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
