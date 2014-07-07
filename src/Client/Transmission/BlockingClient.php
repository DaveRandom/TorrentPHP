<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\BlockingClient as BlockingClientInterface;

class BlockingClient extends Client implements BlockingClientInterface
{
    /**
     * @var BlockingTransport
     */
    private $clientTransport;

    /**
     * Constructor
     *
     * @param BlockingTransport $clientTransport
     */
    public function __construct(BlockingTransport $clientTransport)
    {
        $this->clientTransport = $clientTransport;
    }

    /**
     * {@inheritdoc}
     */
    public function getTorrents(array $ids = [])
    {
        $method = self::METHOD_GET;
        $arguments = $ids ? ['ids' => $ids] : [];

        return $this->clientTransport->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function addTorrent($path)
    {
        $method = self::METHOD_ADD;
        $arguments = array('filename' => $path);

        $response = $this->clientTransport->performRPCRequest($method, $arguments)->getBody();
        $hash = $response->arguments->{'torrent-added'}->hashString;

        return $this->getTorrents([$hash])[0];
    }

    /**
     * {@inheritdoc}
     */
    public function startTorrent($torrent)
    {
        $method = self::METHOD_START;
        $arguments = ['ids' => $this->getTorrentId($torrent)];

        return $this->clientTransport->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function pauseTorrent($torrent)
    {
        $method = self::METHOD_PAUSE;
        $arguments = ['ids' => $this->getTorrentId($torrent)];

        return $this->clientTransport->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrent($torrent)
    {
        $method = self::METHOD_DELETE;
        $arguments = ['ids' => $this->getTorrentId($torrent)];

        return $this->clientTransport->performRPCRequest($method, $arguments)->getBody();
    }
}
