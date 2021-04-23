<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Entity\Event\EventCategory;
use App\OAuth\Model\GrantTypeEnum;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class EventsControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testApiUpcomingEvents()
    {
        $this->client->request(Request::METHOD_GET, '/api/events');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertGreaterThanOrEqual(7, \count(json_decode($content, true)));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
        $this->assertEachJsonItemContainsKey('committee_name', $content, [0, 5]);
        $this->assertEachJsonItemContainsKey('committee_url', $content, [0, 5]);
    }

    /**
     * @dataProvider provideApiEventsCategories
     */
    public function testApiUpcomingEventsForCategory(string $categoryCode, int $expectedCount, array $exclude = [])
    {
        $categoryName = LoadEventCategoryData::LEGACY_EVENT_CATEGORIES[$categoryCode];
        $category = $this->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName]);

        $this->client->request(Request::METHOD_GET, '/api/events?type='.$category->getId());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertCount($expectedCount, \GuzzleHttp\json_decode($content, true));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
        $this->assertEachJsonItemContainsKey('committee_name', $content, $exclude);
        $this->assertEachJsonItemContainsKey('committee_url', $content, $exclude);
    }

    public function testICanRequestMySubscribedEvents()
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_11_UUID,
            'Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ',
            GrantTypeEnum::PASSWORD,
            null,
            'gisele-berthoux@caramail.com',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/events?subscribedOnly', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame(1, $response['metadata']['total_items']);
        self::assertSame('39f25bd2-f866-4c0d-84da-2387898b8db1', $response['items'][0]['uuid']);
    }

    public function provideApiEventsCategories()
    {
        return [
            ['CE011', 0],
            ['CE001', 2, [0]],
            ['CE005', 1],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
