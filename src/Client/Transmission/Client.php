<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\BlockingClient as BlockingClientInterface,
    TorrentPHP\Torrent;

class BlockingClient implements BlockingClientInterface
{
    /**
     * RPC method names
     */
    const METHOD_ADD    = 'torrent-add';
    const METHOD_GET    = 'torrent-get';
    const METHOD_DELETE = 'torrent-remove';
    const METHOD_START  = 'torrent-start';
    const METHOD_PAUSE  = 'torrent-stop';

    /**
     * @var BlockingClient
     */
    private $clientTransport;

    /**
     * Constructor
     *
     * @param BlockingClient $clientTransport
     */
    public function __construct(BlockingClient $clientTransport)
    {
        $this->clientTransport = $clientTransport;
    }

    /**
     * Get a normalised scalar torrent identifier
     *
     * @param Torrent|int|string $torrent
     * @return int|string
     * @throws \InvalidArgumentException
     */
    private function getTorrentId($torrent)
    {
        if ($torrent instanceof Torrent) {
            return $torrent->getHashString();
        } else if (is_string($torrent) || is_int($torrent)) {
            return $torrent;
        }

        throw new \InvalidArgumentException("Unable to extract torrent ID from supplied data: " . (string)$torrent);
    }

    /**
     * {@inheritdoc}
     */
    public function getTorrents(array $ids = [])
    {
        $method = self::METHOD_GET;
        $arguments = $ids ? ['ids' => $ids] : [];

        return $this->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function addTorrent($path)
    {
        $method = self::METHOD_ADD;
        $arguments = array('filename' => $path);

        return $this->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function startTorrent($torrent)
    {
        $method = self::METHOD_START;

        $torrentId = $this->getTorrentId($torrent);
        $arguments = array_merge($this->connectionArgs, ['ids' => $torrentId]);

        return $this->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function pauseTorrent($torrent)
    {
        $method = self::METHOD_PAUSE;

        $torrentId = $this->getTorrentId($torrent);
        $arguments = array_merge($this->connectionArgs, ['ids' => $torrentId]);

        return $this->performRPCRequest($method, $arguments)->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrent($torrent)
    {
        $method = self::METHOD_DELETE;

        $torrentId = $this->getTorrentId($torrent);
        $arguments = array_merge($this->connectionArgs, ['ids' => $torrentId]);

        return $this->performRPCRequest($method, $arguments)->getBody();
    }

}
