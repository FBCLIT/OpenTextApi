<?php

namespace Fbcl\OpenTextApi\Tests;

use Fbcl\OpenTextApi\Client;

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

    }
}
