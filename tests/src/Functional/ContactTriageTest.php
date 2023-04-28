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

    $triageLinkTitle1 = 'Triage link - ' . $this->randomMachineName(8);

    $url_links = [
      [
        'title' => $triageLinkTitle1,
        'uri' => 'https://www.brighton-hove.gov.uk/' . $this->randomMachineName(8),
      ],
    ];
    foreach ($url_links as $url_links) {
      $this->createNode([
        'title' => $url_links['title'],
        'type' => 'contacttriageblock',
        'status' => NodeInterface::PUBLISHED,
      ]);
    }

    // Load the front page.
    $this->drupalGet('<front>');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Test that the block is present.
    $this->assertSession()->pageTextContains($triageLinkTitle1);
  }

}
