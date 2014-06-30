<?php

namespace TorrentPHP;

/**
 * Interface AsyncClient
 *
 * Non-blocking version of BlockingClient.
 *
 * All callbacks will pass the return value to the first argument of the callback, or bool(false) on error
 *
 * @package TorrentPHP
 */
interface AsyncClient
{
    /**
     * Get a list of all torrents from the client
     *
     * @param callable $callback Callback to receive the result data
     * @param array $ids Optional array of id / hashStrings to get data for specific torrents
     */
    public function getTorrents(callable $callback, array $ids = []);

    /**
     * Add a torrent to the client
     *
     * @param string $path The local or remote path to the .torrent file
     * @param callable $callback Callback to receive the result data
     */
    public function addTorrent($path, callable $callback);

    /**
     * Start a torrent
     *
     * @param Torrent|int $torrent A Torrent object or torrent ID
     * @param callable $callback Callback to receive the result data
     */
    public function startTorrent($torrent, callable $callback);

    /**
     * Pause a torrent
     *
     * @param Torrent|int $torrent A Torrent object or torrent ID
     * @param callable $callback Callback to receive the result data
     */
    public function pauseTorrent($torrent, callable $callback);

    /**
     * Delete a torrent - be aware this relates to deleting the torrent file and all files associated with it
     *
     * @param Torrent|int $torrent A Torrent object or torrent ID
     * @param callable $callback Callback to receive the result data
     */
    public function deleteTorrent($torrent, callable $callback);
} 