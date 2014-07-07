<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\ClientException,
    Artax\Response,
    Artax\Client;

/**
 * Class BlockingClient
 *
 * @package TorrentPHP\Client\Transmission
 *
 * @see <https://trac.transmissionbt.com/browser/trunk/extras/rpc-spec.txt>
 */
class BlockingTransport extends Transport
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @constructor
     *
     * @param Client         $client         Artax HTTP Client
     * @param RequestFactory $requestFactory Factory which makes Request objects
     * @param string         $uri            URI for RPC requests
     * @param string         $user           RPC service username
     * @param string         $pass           RPC service username
     */
    public function __construct(Client $client, RequestFactory $requestFactory, $uri, $user = null, $pass = null)
    {
        parent::__construct($requestFactory, $uri, $user, $pass);
        $this->client = $client;
    }

    /**
     * Send a request to the RPC URI using the specified request body
     *
     * @param string $body
     * @return Response
     * @throws ClientException
     */
    private function sendRequest($body)
    {
        $request = $this->requestFactory->createRequest($this->uri, 'POST');

        $request->setAllHeaders($this->requestHeaders);
        $request->setBody($body);

        try {
            return $this->client->request($request);
        } catch (\Artax\ClientException $e) {
            throw new ClientException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Send a JSON RPC request to transmission and get the response JSON
     *
     * @param string $method    The rpc method to call
     * @param array  $arguments Associative array of rpc method arguments to send in the header (not auth arguments)
     * @throws ClientException When something goes wrong with the HTTP call
     * @return mixed The decoded JSON response body
     */
    public function performRPCRequest($method, array $arguments)
    {
        $requestBody = $this->createJSONBody($method, $arguments);

        $response = $this->sendRequest($requestBody);

        if ($response->getStatus() === 409) {
            if (!$response->hasHeader('X-Transmission-Session-Id')) {
                throw new ClientException("Response does not contain an X-Transmission-Session-Id header");
            }

            $this->requestHeaders['X-Transmission-Session-Id'] = $response->getHeader('X-Transmission-Session-Id');
            $response = $this->sendRequest($requestBody);
        }

        if ($response->getStatus() !== 200) {
            throw new ClientException("Unexpected response: {$response->getStatus()}: {$response->getReason()}");
        }

        $result = json_decode($response->getBody());

        $errCode = json_last_error();
        if ($errCode !== JSON_ERROR_NONE) {
            throw new ClientException("Invalid JSON response, error code: {$errCode}");
        }

        return $result;
    }
}
