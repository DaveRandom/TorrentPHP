<?php

namespace TorrentPHP\Client\Transmission;

class Request extends \Artax\Request
{
    public function __construct($uri, $method = 'GET')
    {
        $this->setUri($uri);
        $this->setMethod($method);
    }
}
