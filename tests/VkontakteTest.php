<?php

namespace J4k\OAuth2\Client\Test\Provider;

use J4k\OAuth2\Client\Provider\Vkontakte;
use Mockery as m;

class VkontakteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Vkontakte
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \J4k\OAuth2\Client\Provider\Vkontakte([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'version' => '5.73'
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->getBaseAccessTokenUrl();
        $uri = parse_url($url);

        $this->assertEquals('/access_token', $uri['path']);
    }
}
