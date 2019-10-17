<?php

namespace Fbcl\OpenTextApi;

use GuzzleHttp\ClientInterface;

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
     * Retrieves a nodes meta information.
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
     * Retrieves information on the specified nodes children.
     *
     * @param string $id
     *
     * @return array
     */
    public function getNodeChildren($id)
    {
        return $this->get("nodes/{$id}/nodes");
    }

    /**
     * Creates a new folder in the specified node.
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
     * Creates a new document in the specified node.
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
     * Creates a new node.
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
     * Executes a GET request to the OpenText API.
     *
     * @param string $url
     * @param array  $options
     *
     * @return array
     */
    protected function get($url, array $options = [])
    {
        return $this->decodeResponse(
            $this->client->get($url, $this->defaultOptions($options))->getBody()->getContents()
        );
    }

    /**
     * Executes a POST request to the OpenText API.
     *
     * @param string $url
     * @param array  $options
     *
     * @return array
     */
    protected function post($url, array $options = [])
    {
        return $this->decodeResponse(
            $this->client->post($url, $this->defaultOptions($options))->getBody()->getContents()
        );
    }

    /**
     * Returns the default request options with any given additional options.
     *
     * @param array $additional
     *
     * @return array
     */
    protected function defaultOptions(array $additional = [])
    {
        return ['headers' => ['OTCSTICKET' => $this->token]] + $additional;
    }

    /**
     * Decodes the given response content into an associative array.
     *
     * @param string $content
     *
     * @return array
     */
    protected function decodeResponse($content)
    {
        return json_decode($content, $assoc = true);
    }
}
