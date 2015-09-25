<?php

abstract class AbstractCache
{

    abstract public function store($key, $value, $isUserValue, $userId = null);

    abstract public function fetch($key, $isUserValue, $userId = null);

    abstract public function clear();
}


class DisabledCache extends AbstractCache {
    public function store($key, $value, $isUserValue, $userId = null) {
        return array("found"=>false);
    }

    public function fetch($key, $isUserValue, $userId = null) {
        return false;
    }

    public function clear() {
        return;
    }
}

class XCacheCache extends AbstractCache
{

    private static function getNamespace()
    {
        $CC_CONFIG = Config::getConfig();

        $stationId = $CC_CONFIG['stationId'];
        if (empty($stationId)) {
            throw new Exception("Invalid station id in " . __FUNCTION__ . ": " . $stationId);
        }

        // We use this extra "namespace key" to implement cache clearing.
        $namespaceKeyKey = "namespace_{$stationId}";
        return $namespaceKeyKey;
    }

    private static function generateNamespaceKeyValue()
    {
        return openssl_random_pseudo_bytes(128);
    }

    private function createCacheKey($key, $isUserValue, $userId = null) {

        $CC_CONFIG = Config::getConfig();
        $apiKey = $CC_CONFIG["apiKey"][0];

        $namespaceKeyKey = self::getNamespace();
        //Create the namespace key value if it doesn't exist
        if(!xcache_isset($namespaceKeyKey)) {
            $namespaceKeyVal = self::generateNamespaceKeyValue();
            xcache_set($namespaceKeyKey, $namespaceKeyVal);
        } else {
            $namespaceKeyVal = xcache_get($namespaceKeyKey);
        }

        if ($isUserValue) {
            $cacheKey = "{$namespaceKeyVal}{$key}{$userId}{$apiKey}";
        }
        else {
            $cacheKey = "{$namespaceKeyVal}{$key}{$apiKey}";
        }

        return $cacheKey;
    }

    public function store($key, $value, $isUserValue, $userId = null) {

        $cacheKey = self::createCacheKey($key, $isUserValue, $userId);
        return xcache_set($cacheKey, $value);
    }

    public function fetch($key, $isUserValue, $userId = null) {

        $cacheKey = self::createCacheKey($key, $isUserValue, $userId);
        $found = xcache_isset($cacheKey);
        $value = xcache_get($cacheKey);
        //need to return something to distinguish a cache miss from a stored "false" preference.
        return array(
            "found" => $found,
            "value" => $value,
        );
    }

    public function clear()
    {
        $namespaceKeyKey = self::getNamespace();
        $namespaceKeyVal = self::generateNamespaceKeyValue();
        xcache_unset_by_prefix($namespaceKeyVal);
    }
}


class MemcachedCache extends AbstractCache
{

    private static function getNamespace()
    {
        $CC_CONFIG = Config::getConfig();

        $stationId = $CC_CONFIG['stationId'];
        if (empty($stationId)) {
            throw new Exception("Invalid station id in " . __FUNCTION__ . ": " . $stationId);
        }

        // We use this extra "namespace key" to implement faux cache clearing.
        $namespaceKeyKey = "namespace_{$stationId}";
        return $namespaceKeyKey;
    }

    private static function generateNamespaceKeyValue()
    {
        return rand();
    }

    private function createCacheKey($key, $isUserValue, $userId = null) {

        $CC_CONFIG = Config::getConfig();
        $apiKey = $CC_CONFIG["apiKey"][0];

        $memcached = self::getMemcached();

        $namespaceKeyKey = self::getNamespace();
        $namespaceKeyVal = $memcached->get($namespaceKeyKey);
        //Create the namespace key value if it doesn't exist
        if($namespaceKeyVal===false) {
            $namespaceKeyVal = self::generateNamespaceKeyValue();
            $memcached->set($namespaceKeyKey, $namespaceKeyVal);
        }

        if ($isUserValue) {
            $cacheKey = "{$namespaceKeyVal}{$key}{$userId}{$apiKey}";
        }
        else {
            $cacheKey = "{$namespaceKeyVal}{$key}{$apiKey}";
        }

        return $cacheKey;
    }

    private static function getMemcached() {
        $CC_CONFIG = Config::getConfig();

        static $memcached = null;
        if (is_null($memcached)) {
            $memcached = new Memcached();
            /*
            //$server is in the format "host:port"
            if (!is_null($CC_CONFIG['memcached']['servers'])) {
                foreach ($CC_CONFIG['memcached']['servers'] as $server) {
                    list($host, $port) = explode(":", $server);
                    $memcached->addServer($host, $port);
                }
            }*/
            //$memcached->addServer('', '');
        }
        return $memcached;
    }

    public function store($key, $value, $isUserValue, $userId = null) {

        $cacheKey = self::createCacheKey($key, $isUserValue, $userId);
        $cache = self::getMemcached();
        return $cache->set($cacheKey, $value);
    }

    public function fetch($key, $isUserValue, $userId = null) {

        $cacheKey = self::createCacheKey($key, $isUserValue, $userId);
        $cache = self::getMemcached();
        $found = false;
        $value = $cache->get($cacheKey);
        $result = $cache->getResultCode();
        if ($cache->getResultCode() === Memcached::RES_SUCCESS) {
            $found = true;
        }
        //need to return something to distinguish a cache miss from a stored "false" preference.
        return array(
            "found" => $found,
            "value" => $value,
        );
    }

    public function clear()
    {
        //See "Deleting by Namespace":
        //https://code.google.com/p/memcached/wiki/NewProgrammingTricks
        $memcached = self::getMemcached();
        $namespaceKeyKey = self::getNamespace();
        $namespaceKeyVal = self::generateNamespaceKeyValue();
        $memcached->set($namespaceKeyKey, $namespaceKeyVal);
    }
}
