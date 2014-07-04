<?php

namespace TorrentPHP;

/**
 * Interface BlockingClient
 *
 * If you want to add support for your own torrent client of choice, you need to create a BlockingClient that sends
 * commands to your client. For transmission and vuze, these are RPC calls over the HTTP protocol, but your client may
 * require command-line calls, for example.
 *
 * The BlockingClient is responsible for the actual data retrieval from your client of choice. The responsibility of
 * adapting the responses to create individual Torrent objects by wrapping this class is the ClientAdapter.
 *
 * @package TorrentPHP
 */
interface BlockingClient
{
    /**
     * Get a list of all torrents from the client
     *
     * @param array $ids Optional array of id / hashStrings to get data for specific torrents
     *
     * @throws ClientException  When the client does not return expected 'success' output
     *
     * @return string A JSON string of data
     */
    public function getTorrents(array $ids = []);

    /**
     * Add a torrent to the client
     *
     * @param string $path The local or remote path to the .torrent file
     *
     * @throws ClientException When the client does not return expected 'success' output
     *
     * @return string A JSON string of response data
     */
    public function addTorrent($path);

    /**
     * Start a torrent
     *
     * @param Torrent|int $torrent A Torrent object or torrent ID
     *
     * @throws \InvalidArgumentException When both input arguments are null
     * @throws ClientException           When the client does not return expected output to say that this action succeeded
     *
     * @return string A JSON string of response data
     */
    public function startTorrent($torrent);

    /**
     * Pause a torrent
     *
     * @param Torrent|int $torrent A Torrent object or torrent ID
     *
     * @throws \InvalidArgumentException When both input arguments are null
     * @throws ClientException           When the client does not return expected output to say that this action succeeded
     *
     * @return string A JSON string of response data
     */
    public function pauseTorrent($torrent);

    /**
     * Delete a torrent - be aware this relates to deleting the torrent file and all files associated with it
     *
     * @param Torrent|int $torrent A Torrent object or torrent ID
     *
     * @throws \InvalidArgumentException When both input arguments are null
     * @throws ClientException           When the client does not return expected output to say that this action succeeded
     *
     * @return string A JSON string of response data
     */
    public function deleteTorrent($torrent);
} 