<?php

namespace Oro\Bundle\TagBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ControllersTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_tag_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testIndexJson()
    {
        $response = $this->client->requestGrid(
            'tag-grid',
            [
                'tag-grid[_pager][_page]' => 1,
                'tag-grid[_pager][_per_page]' => 10,
                'tag-grid[_sort_by][name]' => 'DESC'
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);

        $this->assertEquals(0, $result['options']['totalRecords']);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_tag_create'));
        $form = $crawler->selectButton('Save')->form();
        $form['oro_tag_tag_form[name]'] = 'tag758';
        $form['oro_tag_tag_form[owner]'] = 1;
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Tag saved', $crawler->html());
    }

    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'tag-grid',
            ['tag-grid[_filter][name][value]' => 'tag758']
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_tag_update', ['id' => $result['id']])
        );
        $form = $crawler->selectButton('Save')->form();
        $form['oro_tag_tag_form[name]'] = 'tag758_updated';
        $form['oro_tag_tag_form[owner]'] = 1;
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Tag saved', $crawler->html());

        $response = $this->client->requestGrid(
            'tag-grid',
            ['tag-grid[_filter][name][value]' => 'tag758_updated']
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->assertEquals('tag758_updated', $result['name']);
    }

    public function testSearch()
    {
        $response = $this->client->requestGrid(
            'tag-grid',
            ['tag-grid[_filter][name][value]' => 'tag758_updated']
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->client->request('GET', $this->getUrl('oro_tag_search', ['id' => $result['id']]));
        $result = $this->client->getResponse();

        self::assertStringContainsString('Records tagged as "tag758_updated"', $result->getContent());
        self::assertStringContainsString('No results were found', $result->getContent());
    }
}
