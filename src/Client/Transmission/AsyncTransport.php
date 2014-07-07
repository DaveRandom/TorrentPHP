<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\ClientException,
    Artax\AsyncClient,
    Artax\Response;

/**
 * Class AsyncTransport
 *
 * @package TorrentPHP\Client\Transmission
 */
class AsyncTransport extends Transport
{
    /**
     * @var AsyncClient
     */
    private $client;

    /**
     * @constructor
     *
     * @param AsyncClient    $client         Artax Async HTTP Client
     * @param RequestFactory $requestFactory Factory which makes Request objects
     * @param string         $uri            URI for RPC requests
     * @param string         $user           RPC service username
     * @param string         $pass           RPC service username
     */
    public function __construct(AsyncClient $client, RequestFactory $requestFactory, $uri, $user = null, $pass = null)
    {
        parent::__construct($requestFactory, $uri, $user, $pass);
        $this->client = $client;
    }

    /**
     * Send a request to the RPC URI using the specified request body
     *
     * @param string $body
     * @return Request
     */
    private function createRequest($body)
    {
        $request = $this->requestFactory->createRequest($this->uri, 'POST');

        $request->setAllHeaders($this->requestHeaders);
        $request->setBody($body);

        return $request;
    }

    /**
     * Handle the response as a call that may require authentication
     *
     * @param Response $response
     * @param string   $requestBody
     * @param callable $callback
     */
    private function handleFirstResponse(Response $response, $requestBody, callable $callback)
    {
        if ($response->getStatus() !== 409) {
            $this->handleSecondResponse($response, $callback);
            return;
        }

        if (!$response->hasHeader('X-Transmission-Session-Id')) {
            $callback(new ClientException("Response does not contain an X-Transmission-Session-Id header"));
            return;
        }

        $this->requestHeaders['X-Transmission-Session-Id'] = $response->getHeader('X-Transmission-Session-Id');
        $request = $this->createRequest($requestBody);

        $this->client->request($request, function(Response $response) use($callback) {
            $this->handleSecondResponse($response, $callback);
        }, $callback);
    }

    /**
     * Handle the response as a completed RPC call
     *
     * @param Response $response
     * @param callable $callback
     */
    private function handleSecondResponse(Response $response, callable $callback)
    {
        if ($response->getStatus() !== 200) {
            $e = new ClientException("Unexpected response: {$response->getStatus()}: {$response->getReason()}");
            $callback($e);
            return;
        }

        $result = json_decode($response->getBody());

        $errCode = json_last_error();
        if ($errCode !== JSON_ERROR_NONE) {
            $e = new ClientException("Invalid JSON response, error code: {$errCode}");
            $callback($e);
            return;
        }

        $callback($result);
    }

    /**
     * Send a JSON RPC request to transmission and get the response JSON asynchronously
     *
     * The response callback will be passed the response JSON object on success or an Exception on error
     *
     * @param string   $method    The rpc method to call
     * @param array    $arguments Associative array of rpc method arguments to send in the header (not auth arguments)
     * @param callable $callback  Callback to receive the response
     */
    public function performRPCRequest($method, array $arguments, callable $callback)
    {
        $requestBody = $this->createJSONBody($method, $arguments);

        $request = $this->createRequest($requestBody);
        $this->client->request($request, function(Response $response) use($requestBody, $callback) {
            $this->handleFirstResponse($response, $requestBody, $callback);
        }, $callback);
    }
}
