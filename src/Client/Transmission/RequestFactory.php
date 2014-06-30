<?php

namespace TorrentPHP\Client\Transmission;

class RequestFactory
{
    public function createRequest($uri, $method = 'GET')
    {
        return new Request($uri, $method);
    }
} 