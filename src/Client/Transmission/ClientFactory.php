<?php

namespace TorrentPHP\Client\Transmission;

use Artax\Client;

class ClientFactory
{
    public function createBlockingClient($uri, $user = null, $pass = null)
    {
        return new BlockingClient(
            new BlockingTransport(
                new Client,
                new RequestFactory,
                $uri, $user, $pass
            )
        );
    }
}
