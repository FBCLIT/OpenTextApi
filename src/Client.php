<?php

namespace Fbcl\OpenTextApi;

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
     * The Guzzle HTTP client.
     *
     * @var ClientInterface|null
     */
    protected $http;

    /**
     * The OpenText authorization ticket for sending API requests.
     *
     * @var string|null
     */
    protected $ticket;

    /**
     * The locally cached Api instance.
     *
     * @var Api|null
     */
    protected $api;

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
     * Connects to the OpenText API and returns the authorization ticket.
     *
     * @param string $username The username to connect with OpenText
     * @param string $password The password to connect with OpenText
     * @param bool   $ntlm     Whether to use NTLM authentication
     *
     * @return string
     *
     * @throws Exception
     */
    public function connect($username, $password, $ntlm = true)
    {
        $this->ticket = null;

        $config = array_merge($this->config, ['base_uri' => $this->getBaseUrl()]);

        if ($ntlm) {
            $config['auth'] = [$username, $password, 'ntlm'];
        }

        $this->http = $this->getNewHttpClient($config);

        // Send the API authentication attempt.
        $response = $this->http->post('auth', [
            'form_params' => compact('username', 'password'),
        ]);

        return $this->ticket = $this->getTicketFromResponse($response);
    }

    /**
     * Get the retrieved authorization ticket.
     *
     * @return string|null
     */
    public function ticket()
    {
        return $this->ticket;
    }

    /**
     * Get a new OpenText API instance.
     *
     * @return Api
     */
    public function api()
    {
        if (! $this->connected()) {
            throw new Exception("OpenText client has not yet been connected to.");
        }

        return $this->api ? $this->api : $this->api = new Api($this);
    }

    /**
     * Get the underlying Guzzle HTTP client.
     *
     * @return ClientInterface|null
     */
    public function http()
    {
        return $this->http;
    }

    /**
     * Determine if the client has an authorization ticket.
     *
     * @return bool
     */
    public function connected()
    {
        return ! is_null($this->ticket);
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
     * Get the API url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the base URL of the content server API.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $url = rtrim($this->url, '/');

        return "{$url}/api/{$this->version}/";
    }

    /**
     * Get the API version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get a new Guzzle HTTP client.
     *
     * @param array $config
     *
     * @return HttpClient
     */
    protected function getNewHttpClient($config = [])
    {
        return new HttpClient($config);
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
}
