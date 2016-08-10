<?php
namespace Action;

require "BaseAction.php";

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

// $client = new Client(['timeout' => 10]);
$client = new Client([
    'base_uri' => 'http://127.0.0.1/test/index.php',
    'timeout' => 10
]);

// $response = $client->request('GET');

// var_dump($response->getBody()->getContents());
// exit(__FILE__ . __LINE__);


 

$promise = $client->requestAsync('GET', 'http://httpbin.org/get');

$promise->then(function (ResponseInterface $res)
{
    var_dump($res->getBody()->getContents());
    echo $res->getStatusCode() . "\n";
    
}, function (RequestException $e)
{
    echo $e->getMessage() . "\n";
    echo $e->getRequest()
        ->getMethod();
});

echo "do something";

$promise->wait();

echo 22;
