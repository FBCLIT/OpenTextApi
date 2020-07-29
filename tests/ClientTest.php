<?php

namespace Fbcl\OpenTextApi\Tests;

use Exception;
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

        $this->assertNull($client->http());
        $this->assertNull($client->ticket());

        $ticket = $client->connect('username', 'secret');

        $this->assertTrue($client->connected());
        $this->assertEquals('secret-ticket', $ticket);
        $this->assertInstanceOf(Api::class, $client->api());
        $this->assertEquals('secret-ticket', $client->ticket());
    }

    public function test_attempting_to_get_api_throws_exception_with_non_connected_client()
    {
        $this->expectException(Exception::class);

        (new Client('localhost'))->api();
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
