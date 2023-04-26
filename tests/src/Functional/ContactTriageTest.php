<?php

namespace Drupal\Tests\bhcc_contact_triage\Functional;

use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests for the contact triage blocks.
 */
class ContactTriageTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'bhcc_contact_triage',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    // Create a contact triage content type.
    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'title' => 'contact_triage_form',
        'name' => 'Contact triage form',
      ]);
    $type->save();

    // Create an admin user.
    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer blocks',
    ]);

    // Login for the admin user.
    $this->drupalLogin($this->adminUser);

    // Place the contact triage block.
    $this->drupalPlaceBlock('contacttriageblock', []);
  }

  /**
   * Test that the block is displaying.
   */
  public function testContactTriageBlockDisplays() {

    // Create some test nodes.
    $url_links = [
      [
        'title' => 'Brighton & Hove City Council' . $this->randomMachineName(8),
        'uri' => 'https://www.brighton-hove.gov.uk/' . $this->randomMachineName(8),
      ],
    ];
    // Create and test each link.
    foreach ($url_links as $url_links) {
      $node[] = $this->createNode([
        'title' => 'Contact Triage' . $this->randomMachineName(16),
        'type' => 'contact_triage_form',
        'status' => NodeInterface::PUBLISHED,
      ]);

      // Load the front page.
      $this->drupalGet('<front>');
      $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

      // Test that the block is present.
      $this->assertSession()->pageTextContains('What would you like to contact us about?');
    }
  }

}
