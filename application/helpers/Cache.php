<?php

namespace application\helpers;


class Cache
{

    private $_cachepath = CACHE_PATH;
    private $_cachename = 'default';
    private $_extension = '.cache';

    public function __construct($config = null)
    {
        if (true === isset($config)) {
            if (is_string($config)) {
                $this->setCache($config);
            } else if (is_array($config)) {
                $this->setCache($config['name']);
                $this->setCachePath($config['path']);
                $this->setExtension($config['extension']);
            }
        }
    }


    public function isCached($key)
    {
        if ($this->_loadCache() !== false) {
            $cachedData = $this->_loadCache();
            return isset($cachedData[$key]['data']);
        }

        return false;
    }


    public function store($key, $data, $expiration = 0)
    {
        $storeData = array(
            'time' => time(),
            'expire' => $expiration,
            'data' => serialize($data)
        );
        $dataArray = $this->_loadCache();
        if (true === is_array($dataArray)) {
            $dataArray[$key] = $storeData;
        } else {
            $dataArray = array($key => $storeData);
        }
        $cacheData = json_encode($dataArray);
        file_put_contents($this->getCacheDir(), $cacheData);
        return $this;
    }


    public function retrieve($key, $timestamp = false)
    {
        $cachedData = $this->_loadCache();
        (false === $timestamp) ? $type = 'data' : $type = 'time';
        if (!isset($cachedData[$key][$type])) return null;
        return unserialize($cachedData[$key][$type]);
    }


    public function retrieveAll($meta = false)
    {
        if ($meta === false) {
            $results = array();
            $cachedData = $this->_loadCache();
            if ($cachedData) {
                foreach ($cachedData as $k => $v) {
                    $results[$k] = unserialize($v['data']);
                }
            }
            return $results;
        }

        return $this->_loadCache();
    }


    public function erase($key)
    {
        $cacheData = $this->_loadCache();
        if (true === is_array($cacheData)) {
            if (true === isset($cacheData[$key])) {
                unset($cacheData[$key]);
                $cacheData = json_encode($cacheData);
                file_put_contents($this->getCacheDir(), $cacheData);
            } else {
                throw new Exception("Error: erase() - Key '{$key}' not found.");
            }
        }
        return $this;
    }


    public function eraseExpired()
    {
        $cacheData = $this->_loadCache();
        if (true === is_array($cacheData)) {
            $counter = 0;
            foreach ($cacheData as $key => $entry) {
                if (true === $this->_checkExpired($entry['time'], $entry['expire'])) {
                    unset($cacheData[$key]);
                    $counter++;
                }
            }
            if ($counter > 0) {
                $cacheData = json_encode($cacheData);
                file_put_contents($this->getCacheDir(), $cacheData);
            }
            return $counter;
        }
    }


    public function eraseAll()
    {
        $cacheDir = $this->getCacheDir();
        if (true === file_exists($cacheDir)) {
            $cacheFile = fopen($cacheDir, 'w');
            fclose($cacheFile);
        }
        return $this;
    }


    private function _loadCache()
    {
        if (true === file_exists($this->getCacheDir())) {
            $file = file_get_contents($this->getCacheDir());
            return json_decode($file, true);
        }

        return false;
    }


    public function getCacheDir()
    {
        if (true === $this->_checkCacheDir()) {
            $filename = $this->getCache();
            $filename = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($filename));
            return $this->getCachePath() . $this->_getHash($filename) . $this->getExtension();
        }
    }


    private function _getHash($filename)
    {
        return sha1($filename);
    }


    private function _checkExpired($timestamp, $expiration)
    {
        $result = false;
        if ($expiration !== 0) {
            $timeDiff = time() - $timestamp;
            ($timeDiff > $expiration) ? $result = true : $result = false;
        }
        return $result;
    }


    private function _checkCacheDir()
    {
        if (!is_dir($this->getCachePath()) && !mkdir($concurrentDirectory = $this->getCachePath(), 0775, true) && !is_dir($concurrentDirectory)) {
            throw new Exception('Unable to create cache directory ' . $this->getCachePath());
        }

        if (!is_readable($this->getCachePath()) || !is_writable($this->getCachePath())) {
            if (!chmod($this->getCachePath(), 0775)) {
                throw new Exception($this->getCachePath() . ' must be readable and writeable');
            }
        }
        return true;
    }


    public function setCachePath($path)
    {
        $this->_cachepath = $path;
        return $this;
    }


    public function getCachePath()
    {
        return $this->_cachepath;
    }


    public function setCache($name)
    {
        $this->_cachename = $name;
        return $this;
    }


    public function getCache()
    {
        return $this->_cachename;
    }


    public function setExtension($ext)
    {
        $this->_extension = $ext;
        return $this;
    }


    public function getExtension()
    {
        return $this->_extension;
    }
}
