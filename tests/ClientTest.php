<?php

namespace Fbcl\OpenTextApi\Tests;

use Mockery as m;
use Fbcl\OpenTextApi\Api;
use Fbcl\OpenTextApi\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase
{
    public function test_construct()
    {
        $client = new Client('localhost', 'v1');

        $this->assertEquals($client->getUrl(), 'localhost');
        $this->assertEquals($client->getVersion(), 'v1');
        $this->assertEquals($client->getBaseUrl(), 'localhost/api/v1/');

        // Test trimming slashes.
        $client = new Client('localhost///', 'v1');
        $this->assertEquals($client->getBaseUrl(), 'localhost/api/v1/');
    }

    public function test_connect()
    {
        $client = new ConnectClientStub('localhost');

        $api = $client->connect('username', 'secret');

        $this->assertInstanceOf(Api::class, $api);
    }
}

class ConnectClientStub extends Client
{
    protected function getNewHttpClient($config = [])
    {
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->once()->andReturnSelf();
        $response->shouldReceive('getContents')->once()->andReturn('{"ticket":"secret-ticket"}');

        $http = m::mock(ClientInterface::class, $config);
        $http->shouldReceive('post')->once()->withArgs([
            'auth',
            ['form_params' => ['username' => 'username', 'password' => 'secret']],
        ])->andReturn($response);

        return $http;
    }
}
