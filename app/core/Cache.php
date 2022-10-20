<?php

namespace app\core;

/**
 * Class Cache
 */
class Cache
{
    private $path;

    /**
     * Save path to cache dir
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Save cache file
     * @param $key
     * @param $data
     * @param int $seconds
     * @return bool
     */
    public function set($key, $data, int $seconds = 3600): bool
    {
        $content['data'] = $data;
        $content['end_time'] = time() + $seconds;
        if (file_put_contents($this->path . '/' . md5($key) . '.txt', serialize($content))) {
            return true;
        }

        return false;
    }

    /**
     * Return cache file
     * @param $key
     * @return false|mixed
     */
    public function get($key): mixed
    {
        $filePath = $this->path . '/' . md5($key) . '.txt';

        if (file_exists($filePath)) {
            $content = unserialize(file_get_contents($filePath));

            if (time() <= $content['end_time']) {
                return $content['data'];
            }

            unlink($filePath);
        }

        return false;
    }

    /**
     * Delete cache file
     * @param $key
     * @return void
     */
    public function delete($key): void
    {
        $filePath = $this->path . '/' . md5($key) . '.txt';

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}