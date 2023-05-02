<?php

namespace Drupal\Tests\bhcc_contact_triage\Functional;

use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests for the contact triage blocks.
 */
class ContactTriageBlockTest extends BrowserTestBase {

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
    'block',
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
    $this->drupalPlaceBlock('contact_triage_block', []);
  }

  /**
   * Test that the block is displaying.
   */
  public function testContactTriageBlockDisplays() {

    $url_links = [
      [
        'linkText' => 'Triage link 1 - ' . $this->randomMachineName(8),
        'linkURL' => '/' . $this->randomMachineName(8),
      ],
      [
        'linkText' => 'Triage link 2 - ' . $this->randomMachineName(8),
        'linkURL' => '/' . $this->randomMachineName(8),
      ],
      [
        'linkText' => 'Triage link 3 - ' . $this->randomMachineName(8),
        'linkURL' => '/' . $this->randomMachineName(8),
      ],
    ];

    $question = 'Question - ' . $this->randomMachineName(8);
    $node = $this->createNode([
      'title' => $this->randomMachineName(8),
      'type' => 'contact_triage_form',
      'field_form_format' => 'radio',
      'field_contact_triage_question' => $question,
      'field_contact_triage_link' => $url_links,
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Node url.
    $node_url = $node->toUrl()->toString();

    // Load the contact triage node.
    $this->drupalGet($node_url);
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Test that the block is present.
    $this->assertSession()->pageTextContains($question);

    for ($counter = 0; $counter <= 2; $counter++) {
      // For each loop to check each.
      $this->assertSession()->pageTextContains($url_links[$counter]['linkText']);
      $this->submitForm(['links_radios' => $url_links[$counter]['linkURL'] . '~' . $counter], 'Next');
      $this->assertSession()->addressEquals($url_links[$counter]['linkURL']);

      $this->drupalGet($node_url);
    }

  }

}
