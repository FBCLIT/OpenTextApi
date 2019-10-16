# OpenTextApi

A PHP OpenText API client.

## Requirements

- PHP >= 7.1
- ext-json

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
