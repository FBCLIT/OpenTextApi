<?php

namespace Fbcl\OpenTextApi;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class Api
{
    /**
     * The OpenText node types.
     */
    const TYPE_FOLDER = 0;
    const TYPE_DOCUMENT = 144;

    /**
     * The Guzzle HTTP client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * The OpenText API token to utilize.
     *
     * @var string
     */
    protected $token;

    /**
     * Constructor.
     *
     * @param ClientInterface $client
     * @param string          $token
     */
    public function __construct(ClientInterface $client, $token)
    {
        $this->client = $client;
        $this->token = $token;
    }

    /**
     * Get the API token (ticket).
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get the nodes meta information.
     *
     * @param string $id
     *
     * @return array
     */
    public function getNode($id)
    {
        return $this->get("nodes/{$id}");
    }

    /**
     * Get the nodes versions.
     *
     * @param string $id
     *
     * @return array
     */
    public function getNodeVersions($id)
    {
        return $this->get("nodes/{$id}/versions");
    }

    /**
     * Get the nodes content.
     *
     * @param string $id
     *
     * @return array
     */
    public function getNodeContent($id)
    {
        return $this->get("nodes/{$id}/content");
    }

    /**
     * Get the nodes children.
     *
     * @param string $id
     * @param int    $limit
     * @param int    $page
     *
     * @return array
     */
    public function getNodeChildren($id, $limit = 25, $page = 1)
    {
        return $this->get("nodes/{$id}/nodes?limit=$limit&page=$page");
    }

    /**
     * Get the meta information for sub type.
     *
     * @param string $subType
     *
     * @return array
     */
    public function getVolume($subType)
    {
        return $this->get("volumes/{$subType}");
    }

    /**
     * Get the children for the sub type.
     *
     * @param string $subType
     *
     * @return array
     */
    public function getVolumeChildren($subType)
    {
        return $this->get("volumes/{$subType}/nodes");
    }

    /**
     * Create a new folder in the specified node.
     *
     * @param string|int $parentId The parent node ID that the created node will reside in.
     * @param string     $name     The name of the folder.
     *
     * @return array
     */
    public function createNodeFolder($parentId, $name)
    {
        return $this->createNode($parentId, static::TYPE_FOLDER, $name);
    }

    /**
     * Create a new document in the node.
     *
     * @param string|int $parentId The parent node ID that the created node will reside in.
     * @param string     $name     The name of the document.
     * @param string     $path     The path of the file to upload. This must be a directory on the server.
     *
     * @return array
     */
    public function createNodeDocument($parentId, $name, $path)
    {
        $fileParts = explode(DIRECTORY_SEPARATOR, $path);

        return $this->createNode($parentId, static::TYPE_DOCUMENT, $name, [
            'file' => $path,
            'file_filename' => end($fileParts),
        ]);
    }

    /**
     * Create a new node.
     *
     * @param string|int $parentId   The parent node ID that the created node will reside in.
     * @param int        $type       The type of node.
     * @param string     $name       The name of the node.
     * @param array      $additional Additional data to attach to the form params.
     *
     * @return array
     */
    public function createNode($parentId, $type, $name, array $additional = [])
    {
        return $this->post('nodes', [
            'form_params' => [
                    'type' => $type,
                    'parent_id' => $parentId,
                    'name' => $name,
                ] + $additional,
        ]);
    }

    /**
     * Send a GET request to the OpenText API.
     *
     * @param string $url
     * @param array  $options
     *
     * @return mixed
     */
    protected function get($url, array $options = [])
    {
        return $this->decodeResponse(
            $this->client->get($url, $this->appendDefaultOptions($options))
        );
    }

    /**
     * Send a POST request to the OpenText API.
     *
     * @param string $url
     * @param array  $options
     *
     * @return mixed
     */
    protected function post($url, array $options = [])
    {
        return $this->decodeResponse(
            $this->client->post($url, $this->appendDefaultOptions($options))
        );
    }

    /**
     * Get the default request options with any given additional options.
     *
     * @param array $additional
     *
     * @return array
     */
    protected function appendDefaultOptions(array $additional = [])
    {
        return ['headers' => ['OTCSTICKET' => $this->token]] + $additional;
    }

    /**
     * Decode the given response content into an associative array.
     *
     * @param ResponseInterface $response
     *
     * @return array|ResponseInterface
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        if ($this->responseIsJson($response)) {
            return json_decode($response->getBody()->getContents(), $assoc = true);
        }

        return $response;
    }

    /**
     * Determine if the response is JSON.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    protected function responseIsJson(ResponseInterface $response)
    {
        return strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false;
    }
}
