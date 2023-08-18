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
    private $helper;
    /**
     * @var MemcachedFactory
     */
    private $memcachedFactory;
    /**
     * @var PhpFilesFactory
     */
    private $phpFilesFactory;
    /**
     * @var RedisFactory
     */
    private $redisFactory;
    /**
     * @var Watcher
     */
    private $watcher;

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
        $this->helper = $helper;
        $this->watcher = $watcher;
        $this->redisFactory = $redisFactory;
        $this->memcachedFactory = $memcachedFactory;
        $this->phpFilesFactory = $phpFilesFactory;

        $logger = $this->watcher->getLogger();

        $configs = ['fallback_remediation' => $this->helper->getFallbackRemediation()];

        $cacheConfigs = [
            'cache_system' => $this->helper->getCacheTechnology(),
            'fs_cache_path' => Constants::CROWDSEC_ENGINE_CACHE_PATH,
            'memcached_dsn' => $this->helper->getMemcachedDsn(),
            'redis_dsn' => $this->helper->getRedisDsn()
        ];

        $cache = $this->handleCache($cacheConfigs, $logger);

        parent::__construct($configs, $this->watcher, $cache, $logger);
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
                $cache = $this->memcachedFactory->create(['configs' => $configs, 'logger' => $logger]);
                break;
            case Constants::CACHE_SYSTEM_REDIS:
                $cache = $this->redisFactory->create(['configs' => $configs, 'logger' => $logger]);
                break;
            default:
                $cache = $this->phpFilesFactory->create(['configs' => $configs, 'logger' => $logger]);
                break;
        }

        return $cache;
    }
}
