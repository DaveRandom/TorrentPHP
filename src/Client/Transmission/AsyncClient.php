<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\AsyncClient as AsyncClientInterface,
    TorrentPHP\Torrent;

class AsyncClient implements AsyncClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTorrents(callable $callback, array $ids = [])
    {
        // TODO: Implement getTorrents() method.
    }

    /**
     * {@inheritdoc}
     */
    public function addTorrent($path, callable $callback)
    {
        // TODO: Implement addTorrent() method.
    }

    /**
     * {@inheritdoc}
     */
    public function startTorrent($torrent, callable $callback)
    {
        // TODO: Implement startTorrent() method.
    }

    /**
     * {@inheritdoc}
     */
    public function pauseTorrent($torrent, callable $callback)
    {
        // TODO: Implement pauseTorrent() method.
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTorrent($torrent, callable $callback)
    {
        // TODO: Implement deleteTorrent() method.
    }
}
