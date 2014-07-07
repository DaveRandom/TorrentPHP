<?php

namespace TorrentPHP\Client\Transmission;

abstract class Transport
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string[]
     */
    protected $requestHeaders = [
        'Content-Type' => 'application/json; charset=utf-8',
    ];

    /**
     * @constructor
     *
     * @param RequestFactory $requestFactory Factory which makes Request objects
     * @param string         $uri            URI for RPC requests
     * @param string         $user           RPC service username
     * @param string         $pass           RPC service username
     */
    public function __construct(RequestFactory $requestFactory, $uri, $user = null, $pass = null)
    {
        $this->requestFactory = $requestFactory;
        $this->uri = $uri;

        if ($user !== null || $pass !== null) {
            $this->requestHeaders['Authorization'] = 'Basic ' . base64_encode($user . ':' . $pass);
        }
    }

    /**
     * Create a JSON string for a raw request body
     *
     * @param string $method
     * @param array $arguments
     * @return string
     */
    protected function createJSONBody($method, array $arguments)
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
}
