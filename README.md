# OpenTextApi

A PHP OpenText API client.

## Requirements

- PHP >= 7.1
- ext-json

You must also enable the REST API in your administrator configuration.

This is usually located at:

```
https://myserver.com/otcs/cs.exe?func=enterprisearchivesp.restapiconfig
```

> Enable the REST API (default): [x]

## Installation

```bash
composer require fbclit/opentextapi
```

## Usage

```php
<?php

require_once('vendor/autoload.php');

use Fbcl\OpenTextApi\Client;

$client = new Client('https://server.com/otcs/cs.exe', 'v1');

try {
    $api = $client->connect('username', 'secret', $ntlm = true);

    $api->getNode('123456');
} catch (\Exception $ex) {
    //
}
```

### Uploading a File

OpenText does not support uploading files normally through POST requests. It
only supports streamed file uploads through asynchronous JavaScript requests.

To perform a file upload, you **must** place the file on the web server
server in your OpenText configured `Uploads` directory. This directory
is configured in the admin web interface at:

https://server.com/otcs/cs.exe?func=admin.sysvars

> Upload Directory
> Enter the directory specification for temporary storage of documents added
> to Content Server (optional). If specified, the Content Server will only
> add uploaded files found in this directory.

Usually this is set to the D:\Upload folder.

```php
<?php

use Fbcl\OpenTextApi\Client;

$client = new Client('http://server.com/otcs/cs.exe', 'v1');

$api = $client->connect('username', 'secret');

try {
    // The folder node ID of where the file will be created under.
    $parentNodeId = '12356';

    // The file name to display in OpenText
    $fileName = 'My Document.txt';

    // The actual file path of the file on the OpenText server.
    $serverFilePath = 'D:\Upload\My Document.txt';
    
    $response = $api->createNodeDocument($parentNodeId, $fileName, $serverFilePath);

    if (isset($response['id'])) {
        // The ID of the newly created document will be returned.
        echo $response['id']; 
    }   
} catch (\Exception $ex) {
    // File not found on server drive, or issue creating node from given parent.
}
```

