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

namespace CrowdSec\Engine\Plugin;

use CrowdSec\Bouncer\Constants;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\RemediationEngine\CacheStorage\AbstractCache;
use CrowdSec\RemediationEngine\CacheStorage\MemcachedFactory;
use CrowdSec\RemediationEngine\CacheStorage\PhpFilesFactory;
use CrowdSec\RemediationEngine\CacheStorage\RedisFactory;
use Exception;
use Magento\Config\Model\Config as MagentoConfig;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Psr\Cache\InvalidArgumentException;
use CrowdSec\Common\Exception as CrowdSecException;

/**
 * Plugin to handle crowdsec section config updates
 */
class Config
{

    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var MemcachedFactory
     */
    private $memcachedFactory;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var PhpFilesFactory
     */
    private $phpFilesFactory;
    /**
     * @var RedisFactory
     */
    private $redisFactory;

    /**
     * Constructor
     *
     * @param ManagerInterface $messageManager
     * @param Helper $helper
     * @param MemcachedFactory $memcachedFactory
     * @param RedisFactory $redisFactory
     * @param PhpFilesFactory $phpFilesFactory
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ManagerInterface $messageManager,
        Helper $helper,
        MemcachedFactory $memcachedFactory,
        RedisFactory $redisFactory,
        PhpFilesFactory $phpFilesFactory,
        WriterInterface $configWriter
    ) {
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->memcachedFactory = $memcachedFactory;
        $this->redisFactory = $redisFactory;
        $this->phpFilesFactory = $phpFilesFactory;
        $this->configWriter = $configWriter;
    }

    /**
     * Handle admin CrowdSec section changes
     *
     * @param MagentoConfig $subject
     * @return null
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function beforeSave(
        MagentoConfig $subject
    ) {
        if ($subject->getSection() === Helper::SECTION) {
            // Retrieve saved values (old) and posted data (new)
            $oldMemcachedDsn = $this->helper->getMemcachedDSN();
            $newMemcachedDsn = $this->getCurrentValue(
                $subject->getData(Helper::MEMCACHED_DSN_FULL_PATH),
                $oldMemcachedDsn
            );
            $oldRedisDsn = $this->helper->getRedisDSN();
            $newRedisDsn = $this->getCurrentValue($subject->getData(Helper::REDIS_DSN_FULL_PATH), $oldRedisDsn);
            $oldCacheSystem = $this->helper->getCacheTechnology();
            $newCacheSystem = $this->getCurrentValue(
                $subject->getData(Helper::CACHE_TECHNOLOGY_FULL_PATH),
                $oldCacheSystem
            );
            $oldRefreshCronExpr = $this->helper->getRefreshCronExpr();
            $newRefreshCronExpr = $this->getCurrentValue(
                $subject->getData(Helper::REFRESH_CRON_EXPR_FULL_PATH),
                $oldRefreshCronExpr
            );
            $oldPruneCronExpr = $this->helper->getPruneCronExpr();
            $newPruneCronExpr = $this->getCurrentValue(
                $subject->getData(Helper::PRUNE_CRON_EXPR_FULL_PATH),
                $oldPruneCronExpr
            );
            $oldPushSignalsCronExpr = $this->helper->getPushSignalsCronExpr();
            $newPushSignalsCronExpr = $this->getCurrentValue(
                $subject->getData(Helper::PUSH_SIGNALS_CRON_EXPR_FULL_PATH),
                $oldPushSignalsCronExpr
            );
            $oldCleanEventsCronExpr = $this->helper->getCleanEventsCronExpr();
            $newCleanEventsCronExpr = $this->getCurrentValue(
                $subject->getData(Helper::CLEAN_EVENTS_CRON_EXPR_FULL_PATH),
                $oldCleanEventsCronExpr
            );

            $cacheOptions = $this->helper->getCacheSystemOptions();
            $newCacheLabel = $cacheOptions[$newCacheSystem] ?? __('Unknown');
            $hasCacheSystemChanged = $oldCacheSystem !== $newCacheSystem;
            $hasDsnChanged = $this->hasDsnChanged(
                $newCacheSystem,
                $oldRedisDsn,
                $newRedisDsn,
                $oldMemcachedDsn,
                $newMemcachedDsn
            );
            $cacheChanged = ($hasCacheSystemChanged || $hasDsnChanged);
            $refreshCronExprChanged = $oldRefreshCronExpr !== $newRefreshCronExpr;
            $pruneCronExprChanged = $oldPruneCronExpr !== $newPruneCronExpr;
            $pushSignalsCronExprChanged = $oldPushSignalsCronExpr !== $newPushSignalsCronExpr;
            $cleanEventsCronExprChanged = $oldCleanEventsCronExpr !== $newCleanEventsCronExpr;

            // We should have to test crons
            $this->_handleCronExpr($refreshCronExprChanged, $newRefreshCronExpr);
            $this->_handleCronExpr($pushSignalsCronExprChanged, $newPushSignalsCronExpr);
            $this->_handleCronExpr($cleanEventsCronExprChanged, $newCleanEventsCronExpr);
            // We should have to disable or test cron
            $this->_handlePruneCronExpr($oldCacheSystem, $newCacheSystem, $pruneCronExprChanged, $newPruneCronExpr);
            // We should have to test new cache
            $this->_handleTestCache($cacheChanged, $newCacheSystem, $newMemcachedDsn, $newRedisDsn, $newCacheLabel);
        }

        return null;
    }

    /**
     * Handle refresh expression cron
     *
     * @param bool $cronExprChanged
     * @param string $newCronExpr
     * @return void
     * @throws \Exception
     */
    private function _handleCronExpr(
        bool $cronExprChanged,
        string $newCronExpr
    ) {
        if ($cronExprChanged) {
            // Check expression
            try {
                $this->helper->validateCronExpr($newCronExpr);
            } catch (Exception $e) {
                $this->messageManager->getMessages(true);
                throw new CrowdSecException("Cron expression \"$newCronExpr\" is not valid.");
            }
        }
    }

    /**
     * Handle prune cron expression cron
     *
     * @param string $oldCacheSystem
     * @param string $newCacheSystem
     * @param bool $cronExprChanged
     * @param string $newCronExpr
     * @return void
     * @throws Exception
     */
    private function _handlePruneCronExpr(
        string $oldCacheSystem,
        string $newCacheSystem,
        bool $cronExprChanged,
        string $newCronExpr
    ) {
        if ($oldCacheSystem !== $newCacheSystem &&
            $newCacheSystem !== Constants::CACHE_SYSTEM_PHPFS
            && $newCronExpr !== \CrowdSec\Engine\Constants::CRON_DISABLE) {
            // Disable cache pruning cron if cache technology is not file system
            try {
                $this->configWriter->save(
                    Helper::XML_PATH_CRON_PRUNE_CACHE_EXPR,
                    \CrowdSec\Engine\Constants::CRON_DISABLE
                );
                $cronMessage =
                    __('As the cache system is not File system anymore, cache pruning cron has been disabled.');
                $this->messageManager->addNoticeMessage($cronMessage);
            } catch (Exception $e) {
                throw new \Exception('Disabled pruning cron expression can\'t be saved: ' . $e->getMessage());
            }
        } else {
            // Check expression
            $this->_handleCronExpr($cronExprChanged, $newCronExpr);
        }
    }

    /**
     * Test a cache configuration for some bouncer
     *
     * @param bool $cacheChanged
     * @param string $cacheSystem
     * @param string $memcachedDsn
     * @param string $redisDsn
     * @param Phrase $cacheLabel
     * @throws Exception
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function _handleTestCache(
        bool $cacheChanged,
        string $cacheSystem,
        string $memcachedDsn,
        string $redisDsn,
        Phrase $cacheLabel
    ): void {
        if ($cacheChanged) {
            try {
                // Try the adapter connection (Redis or Memcached will crash if the connection is incorrect)
                switch ($cacheSystem) {
                    case Constants::CACHE_SYSTEM_REDIS:
                        $configs = ['redis_dsn' => $redisDsn];
                        $cache = $this->redisFactory->create(['configs' => $configs]);
                        break;
                    case Constants::CACHE_SYSTEM_MEMCACHED:
                        $configs = ['memcached_dsn' => $memcachedDsn];
                        $cache = $this->memcachedFactory->create(['configs' => $configs]);
                        break;
                    case Constants::CACHE_SYSTEM_PHPFS:
                        $configs = ['fs_cache_path' => Constants::CROWDSEC_CACHE_PATH];
                        $cache = $this->phpFilesFactory->create(['configs' => $configs]);
                        break;
                    default:
                        return;
                }

                $this->testCacheConnection($cache);
                $cacheMessage = __('CrowdSec new cache (%1) has been successfully tested.', $cacheLabel);
                $this->messageManager->addNoticeMessage($cacheMessage);
            } catch (Exception $e) {
                $this->helper->getLogger()->error('Error while testing cache', [
                    'type' => 'M2_EXCEPTION_WHILE_TESTING_CACHE',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                $cacheMessage =
                    "Technical error while testing the $cacheLabel cache: " . $e->getMessage();
                throw new CrowdSecException($cacheMessage);
            }
        }
    }

    /**
     * Get a configuration current value
     *
     * @param mixed $subject
     * @param mixed $saved
     * @return mixed
     */
    private function getCurrentValue($subject, $saved)
    {
        return $subject ?: $saved;
    }

    /**
     * Check if DNS configuration has changed
     *
     * @param string $newCacheSystem
     * @param string $oldRedisDsn
     * @param string $newRedisDsn
     * @param string $oldMemcachedDsn
     * @param string $newMemcachedDsn
     * @return bool
     */
    private function hasDsnChanged(
        string $newCacheSystem,
        string $oldRedisDsn,
        string $newRedisDsn,
        string $oldMemcachedDsn,
        string $newMemcachedDsn
    ): bool {
        switch ($newCacheSystem) {
            case Constants::CACHE_SYSTEM_REDIS:
                return $oldRedisDsn !== $newRedisDsn;
            case Constants::CACHE_SYSTEM_MEMCACHED:
                return $oldMemcachedDsn !== $newMemcachedDsn;
            default:
                return false;
        }
    }

    /**
     * Test cache.
     *
     * @param AbstractCache $cache
     * @return void
     * @throws InvalidArgumentException
     */
    private function testCacheConnection(AbstractCache $cache): void
    {
        try {
            $cache->getItem(AbstractCache::CONFIG);
        } catch (\Exception $e) {
            throw new \Exception(
                'Error while testing cache connection: ' . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }
}
