<?php

namespace Oro\Bundle\SidebarBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class SidebarTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
    }

    /**
     * @dataProvider positionsPostProvider
     */
    public function testGetInitialPositions($position)
    {
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_sidebars', array('position' => $position['position']))
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->assertEmpty($result->getContent());
    }

    /**
     * @depends testGetInitialPositions
     * @dataProvider positionsPostProvider
     */
    public function testPostPosition($position)
    {
        $this->client->jsonRequest(
            'POST',
            $this->getUrl('oro_api_post_sidebars'),
            $position
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);
        $this->assertGreaterThan(0, $result['id']);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_sidebars', array('position' => $position['position']))
        );

        $actualResult = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEquals(array_merge($result, $position), $actualResult);
    }

    /**
     * @depends testPostPosition
     * @dataProvider positionsPutProvider
     */
    public function testPutPositions($position)
    {
        // get sidebar id
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_sidebars', array('position' => $position['position']))
        );

        $actualResult = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $position = array_merge(array('id' => $actualResult['id']), $position);
        $this->assertNotEquals($position, $actualResult);

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('oro_api_put_sidebars', array('stateId' =>  $position['id'])),
            $position
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);

        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_sidebars', array('position' => $position['position']))
        );

        $actualResult = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($position, $actualResult);
    }

    public function positionsPostProvider()
    {
        return array(
            array(
          'left-maximized' => array(
              'position' => 'SIDEBAR_LEFT',
              'state' => 'SIDEBAR_MAXIMIZED'
            )),
            array(
            'right-maximized' => array(
                'position' => 'SIDEBAR_RIGHT',
                'state' => 'SIDEBAR_MAXIMIZED'
            ))
        );
    }
    public function positionsPutProvider()
    {
        return array(
            array(
                'left-minimized' => array(
                    'position' => 'SIDEBAR_LEFT',
                    'state' => 'SIDEBAR_MINIMIZED'
                )
            ),
            array(
                'left-maximized' => array(
                    'position' => 'SIDEBAR_LEFT',
                    'state' => 'SIDEBAR_MAXIMIZED'
                )
            ),
            array(
                'right-minimized' => array(
                    'position' => 'SIDEBAR_RIGHT',
                    'state' => 'SIDEBAR_MINIMIZED'
                )
            ),
            array(
                'right-maximized' => array(
                    'position' => 'SIDEBAR_RIGHT',
                    'state' => 'SIDEBAR_MAXIMIZED'
                )
            ),
        );
    }
}
