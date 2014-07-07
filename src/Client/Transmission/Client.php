<?php

namespace TorrentPHP\Client\Transmission;

use TorrentPHP\Torrent;

abstract class Client
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
     * Get a normalised scalar torrent identifier
     *
     * @param Torrent|int|string $torrent
     * @return int|string
     * @throws \InvalidArgumentException
     */
    protected function getTorrentId($torrent)
    {
        if ($torrent instanceof Torrent) {
            return $torrent->getHashString();
        } else if (is_string($torrent) || is_int($torrent)) {
            return $torrent;
        }

        throw new \InvalidArgumentException("Unable to extract torrent ID from supplied data: " . (string)$torrent);
    }
}
