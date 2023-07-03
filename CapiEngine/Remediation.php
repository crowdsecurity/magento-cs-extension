<?php declare(strict_types=1);
/**
 * CrowdSec_Engine Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT LICENSE
 * that is bundled with this package in the file LICENSE
 *
 * @category   CrowdSec
 * @package    CrowdSec_Engine
 * @copyright  Copyright (c)  2023+ CrowdSec
 * @author     CrowdSec team
 * @see        https://crowdsec.net CrowdSec Official Website
 * @license    MIT LICENSE
 *
 */

/**
 *
 * @category CrowdSec
 * @package  CrowdSec_Engine
 * @module   Engine
 * @author   CrowdSec team
 *
 */

namespace CrowdSec\Engine\CapiEngine;

use CrowdSec\RemediationEngine\CapiRemediation;
use CrowdSec\RemediationEngine\CacheStorage\AbstractCache;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Constants;
use Psr\Log\LoggerInterface;
use CrowdSec\RemediationEngine\CacheStorage\PhpFilesFactory;
use CrowdSec\RemediationEngine\CacheStorage\RedisFactory;
use CrowdSec\RemediationEngine\CacheStorage\MemcachedFactory;

class Remediation extends CapiRemediation
{
    /**
     * @var Helper
     */
    private $_helper;
    /**
     * @var MemcachedFactory
     */
    private $_memcachedFactory;
    /**
     * @var PhpFilesFactory
     */
    private $_phpFilesFactory;
    /**
     * @var RedisFactory
     */
    private $_redisFactory;
    /**
     * @var Watcher
     */
    private $_watcher;

    /**
     * Constructor.
     *
     * @param Watcher $watcher
     * @param Helper $helper
     * @param PhpFilesFactory $phpFilesFactory
     * @param RedisFactory $redisFactory
     * @param MemcachedFactory $memcachedFactory
     */
    public function __construct(
        Watcher $watcher,
        Helper $helper,
        PhpFilesFactory $phpFilesFactory,
        RedisFactory $redisFactory,
        MemcachedFactory $memcachedFactory
    ) {
        $this->_helper = $helper;
        $this->_watcher = $watcher;
        $this->_redisFactory = $redisFactory;
        $this->_memcachedFactory = $memcachedFactory;
        $this->_phpFilesFactory = $phpFilesFactory;

        $logger = $this->_watcher->getLogger();

        $cacheConfigs = [
            'cache_system' => $this->_helper->getCacheTechnology(),
            'fs_cache_path' => Constants::CROWDSEC_ENGINE_CACHE_PATH,
            'memcached_dsn' => $this->_helper->getMemcachedDsn(),
            'redis_dsn' => $this->_helper->getRedisDsn()
        ];

        $cache = $this->handleCache($cacheConfigs, $logger);

        parent::__construct([], $this->_watcher, $cache, $logger);
    }

    /**
     * Instantiate cache depending on settings.
     *
     * @param array $configs
     * @param LoggerInterface $logger
     * @return AbstractCache
     */
    private function handleCache(array $configs, LoggerInterface $logger): AbstractCache
    {
        $cacheSystem = $configs['cache_system'] ?? Constants::CACHE_SYSTEM_PHPFS;
        switch ($cacheSystem) {
            case Constants::CACHE_SYSTEM_MEMCACHED:
                $cache = $this->_memcachedFactory->create(['configs' => $configs, 'logger' => $logger]);
                break;
            case Constants::CACHE_SYSTEM_REDIS:
                $cache = $this->_redisFactory->create(['configs' => $configs, 'logger' => $logger]);
                break;
            default:
                $cache = $this->_phpFilesFactory->create(['configs' => $configs, 'logger' => $logger]);
                break;
        }

        return $cache;
    }
}
