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
class BlockingTransport
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string[]
     */
    private $requestHeaders = [
        'Content-Type' => 'application/json; charset=utf-8',
    ];

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
        $this->requestFactory = $requestFactory;
        $this->client = $client;
        $this->uri = $uri;

        if ($user || $pass) {
            $this->requestHeaders['Authorization'] = 'Basic ' . base64_encode($user . ':' . $pass);
        }
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
     * Create a JSON string for a raw request body
     *
     * @param string $method
     * @param array $arguments
     * @return string
     */
    private function createJSONBody($method, $arguments)
    {
        return json_encode([
            'method'    => $method,
            'arguments' => array_merge(
                [
                    'fields' => [
                        'hashString', 'name', 'sizeWhenDone', 'status', 'rateDownload', 'rateUpload',
                        'uploadedEver', 'files', 'errorString',
                    ],
                ],
                $arguments
            )
        ]);
    }

    /**
     * Helper method to facilitate json rpc requests using the Artax client
     *
     * @param string $method    The rpc method to call
     * @param array  $arguments Associative array of rpc method arguments to send in the header (not auth arguments)
     * @throws ClientException When something goes wrong with the HTTP call
     * @return Response The HTTP response containing headers / body ready for validation / parsing
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
            throw new ClientException(sprintf(
                '"%s" expected 200 response, got "%s" instead, reason: "%s"',
                $method, $response->getStatus(), $response->getReason()
            ));
        }

        $body = $response->getBody();

        json_decode($body);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ClientException(sprintf(
                '"%s" did not get back a JSON response body, got "%s" instead',
                $method, print_r($response->getBody(), true)
            ));
        }

        return $response;
    }
}
