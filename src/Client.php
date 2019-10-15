<?php

namespace Fbcl\OpenText;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * The OpenText API url.
     *
     * @var string
     */
    protected $url;

    /**
     * The OpenText API version.
     *
     * @var string
     */
    protected $version;

    /**
     * The Guzzle HTTP client configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param string $url     The OpenText API url.
     * @param string $version The OpenText API version.
     */
    public function __construct($url, $version = 'v1')
    {
        $this->url = $url;
        $this->version = $version;
    }

    /**
     * Connects to the OpenText API.
     *
     * @param string $username The username to connect with OpenText
     * @param string $password The password to connect with OpenText
     * @param bool   $ntlm     Whether to use NTLM authentication
     *
     * @return Api
     *
     * @throws Exception
     */
    public function connect($username, $password, $ntlm = true)
    {
        $config = array_merge($this->config, ['base_uri' => $this->getBaseUrl()]);

        if ($ntlm) {
            $config['auth'] = [$username, $password, 'ntlm'];
        }

        $client = new HttpClient($config);

        // Send the API authentication attempt.
        $response = $client->post('auth', [
            'form_params' => compact('username', 'password'),
        ]);

        $ticket = $this->getTicketFromResponse($response);

        return $this->getNewApiClient($client, $ticket);
    }

    /**
     * Set the Guzzle HTTP client config.
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get a new API client.
     *
     * @param ClientInterface $client The Guzzle HTTP client.
     * @param string          $ticket The OpenText API ticket.
     *
     * @return Api
     */
    protected function getNewApiClient(ClientInterface $client, $ticket)
    {
        return new Api($client, $ticket);
    }

    /**
     * Get the authentication ticket from the response.
     *
     * @param ResponseInterface $response
     *
     * @return string The OTSC API ticket.
     *
     * @throws Exception When the response does not contain a API ticket.
     */
    protected function getTicketFromResponse(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), $assoc = true);

        if (isset($body['ticket'])) {
            return $body['ticket'];
        }

        throw new Exception("Unable to retrieve OTCS API ticket.");
    }

    /**
     * Get the base URL of the content server API.
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        $url = rtrim($this->url, '/');

        return "{$url}/api/{$this->version}/";
    }
}
