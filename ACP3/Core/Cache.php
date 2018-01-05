<?php
namespace ACP3\Core;

use ACP3\Core\Cache\CacheDriverFactory;

class Cache
{
    /**
     * @var \ACP3\Core\Cache\CacheDriverFactory
     */
    protected $cacheDriverFactory;
    /**
     * @var string
     */
    protected $namespace = '';
    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    protected $driver;

    /**
     * Cache constructor.
     *
     * @param \ACP3\Core\Cache\CacheDriverFactory $cacheDriverFactory
     * @param string                              $namespace
     */
    public function __construct(CacheDriverFactory $cacheDriverFactory, $namespace)
    {
        $this->cacheDriverFactory = $cacheDriverFactory;
        $this->namespace = $namespace;
    }

    /**
     * @param string $cacheId
     *
     * @return bool|array|string
     */
    public function fetch($cacheId)
    {
        return $this->getDriver()->fetch($cacheId);
    }

    /**
     * @param string $cacheId
     *
     * @return bool
     */
    public function contains($cacheId)
    {
        return $this->getDriver()->contains($cacheId);
    }

    /**
     * @param string $cacheId
     * @param mixed  $data
     * @param int    $lifetime
     *
     * @return bool
     */
    public function save($cacheId, $data, $lifetime = 0)
    {
        return $this->getDriver()->save($cacheId, $data, $lifetime);
    }

    /**
     * @param string $cacheId
     *
     * @return bool
     */
    public function delete($cacheId)
    {
        return $this->getDriver()->delete($cacheId);
    }

    /**
     * @return bool
     */
    public function deleteAll()
    {
        return $this->getDriver()->deleteAll();
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return $this->getDriver()->flushAll();
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function getDriver()
    {
        if ($this->driver === null) {
            $this->driver = $this->cacheDriverFactory->create($this->namespace);
        }

        return $this->driver;
    }
}
