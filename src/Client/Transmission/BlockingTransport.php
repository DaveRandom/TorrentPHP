<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\ClientException,
    Artax\Response,
    Artax\Request,
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
    protected $client;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var string
     */
    protected $rpcUri;

    /**
     * @var string
     */
    protected $authHeader;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @constructor
     *
     * @param Client           $client         Artax HTTP Client
     * @param RequestFactory   $requestFactory Factory which makes Request objects
     * @param ConnectionConfig $config         Configuration object used to connect over rpc
     */
    public function __construct(Client $client, RequestFactory $requestFactory, ConnectionConfig $config)
    {
        $this->connectionArgs = $config->getArgs();
        $this->$requestFactory = $requestFactory;
        $this->client = $client;

        $args = $config->getArgs();
        $this->rpcUri = sprintf('%s:%s/transmission/rpc', $args['host'], $args['port']);
        $authString = sprintf('%s:%s', $args['username'], $args['password']);
        $this->authHeader = sprintf('Basic %s', base64_encode($authString));
    }

    private function createRPCRequest($body)
    {
        $request = $this->requestFactory->createRequest($this->rpcUri, 'POST');

        $request->setAllHeaders([
            'Content-Type'              => 'application/json; charset=utf-8',
            'Authorization'             => $this->authHeader,
            'X-Transmission-Session-Id' => $this->sessionId,
        ]);
        $request->setBody($body);

        return $body;
    }

    /**
     * Helper method to facilitate json rpc requests using the Artax client
     *
     * @param string $method    The rpc method to call
     * @param array  $arguments Associative array of rpc method arguments to send in the header (not auth arguments)
     *
     * @throws ClientException When something goes wrong with the HTTP call
     *
     * @return Response The HTTP response containing headers / body ready for validation / parsing
     */
    public function performRPCRequest($method, array $arguments)
    {
        $requestBody = json_encode([
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

        try {
            $request = $this->createRPCRequest($requestBody);
            $response = $this->client->request($request);
        } catch (\Artax\ClientException $e) {
            throw new ClientException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatus() !== 200) {
            if ($response->getStatus() === 409) {
                if (!$response->hasHeader('X-Transmission-Session-Id')) {
                    throw new ClientException("Response does not contain an X-Transmission-Session-Id header");
                }
            }
        }

        $sessionId = $response->getHeader('X-Transmission-Session-Id');
        $requestBody = json_encode([
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

        $request->setMethod('POST');
        $request->setHeader('X-Transmission-Session-Id', $sessionId);
        $request->setBody($requestBody);

        try {
            $response = $this->client->request($request);
        } catch (\Artax\ClientException $e) {
            throw new ClientException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
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