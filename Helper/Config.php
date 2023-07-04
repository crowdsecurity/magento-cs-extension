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

namespace CrowdSec\Engine\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{

    public const CACHE_TECHNOLOGY_FULL_PATH = 'groups/decisions/groups/cache/fields/technology/value';
    public const CLEAN_EVENTS_CRON_EXPR_FULL_PATH = 'groups/crons/groups/events/fields/clean_expr/value';
    public const MEMCACHED_DSN_FULL_PATH = 'groups/decisions/groups/cache/fields/memcached_dsn/value';
    public const PRUNE_CRON_EXPR_FULL_PATH = 'groups/crons/groups/cache/fields/prune_expr/value';
    public const PUSH_SIGNALS_CRON_EXPR_FULL_PATH = 'groups/crons/groups/signals/fields/push_expr/value';
    public const REDIS_DSN_FULL_PATH = 'groups/decisions/groups/cache/fields/redis_dsn/value';
    public const REFRESH_CRON_EXPR_FULL_PATH = 'groups/crons/group/cache/fields/refresh_expr/value';
    public const SECTION = 'crowdsec_engine';
    public const XML_PATH_API_TIMEOUT = self::SECTION . '/general/api_timeout';
    public const XML_PATH_CRON_CLEAN_EVENTS_EXPR = self::SECTION . '/crons/events/clean_expr';
    public const XML_PATH_CRON_PRUNE_CACHE_EXPR = self::SECTION . '/crons/cache/prune_expr';
    public const XML_PATH_CRON_PUSH_SIGNALS_EXPR = self::SECTION . '/crons/signals/push_expr';
    public const XML_PATH_CRON_REFRESH_CACHE_EXPR = self::SECTION . '/crons/cache/refresh_expr';
    public const XML_PATH_DECISIONS_BAN_LOCALLY = self::SECTION . '/decisions/ban_locally';
    public const XML_PATH_DECISIONS_BOUNCE_BAN = self::SECTION . '/decisions/bounce_ban';
    public const XML_PATH_DECISIONS_CACHE_MEMCACHED_DSN = self::SECTION . '/decisions/cache/memcached_dsn';
    public const XML_PATH_DECISIONS_CACHE_REDIS_DSN = self::SECTION . '/decisions/cache/redis_dsn';
    public const XML_PATH_DECISIONS_CACHE_TECHNOLOGY = self::SECTION . '/decisions/cache/technology';
    public const XML_PATH_ENV = self::SECTION . '/general/environment';
    public const XML_PATH_EVENT_LIFETIME = self::SECTION . '/crons/events/lifetime';
    public const XML_PATH_LOG_LEVEL = self::SECTION . '/general/log_level';
    public const XML_PATH_SIGNALS_BAN_DURATION = self::SECTION . '/signals/ban_duration';
    public const XML_PATH_SIGNAL_SCENARIOS = self::SECTION . '/signals/scenarios';
    public const XML_PATH_SUBSCRIBED_SCENARIOS = self::SECTION . '/decisions/subscribed_scenarios';
    /**
     * @var array
     */
    protected $_globals = [
        'api_timeout' => null,
        'ban_duration' => null,
        'ban_locally' => null,
        'bounce_ban' => null,
        'cache_technology' => null,
        'clean_events_expr' => null,
        'env' => null,
        'event_lifetime' => null,
        'log_level' => null,
        'memcached_dsn' => null,
        'prune_cache_expr' => null,
        'push_signals_expr' => null,
        'redis_dsn' => null,
        'refresh_cache_expr' => null,
        'scenario_enabled' => [],
        'signal_scenarios' => null,
        'subscribed_scenarios' => null
    ];

    /**
     * Get api timeout config.
     *
     * @return int
     */
    public function getApiTimeout(): int
    {
        if (!isset($this->_globals['api_timeout'])) {
            $this->_globals['api_timeout'] = (int)$this->scopeConfig->getValue(self::XML_PATH_API_TIMEOUT);
        }

        return (int)$this->_globals['api_timeout'];
    }

    /**
     * Get ban duration config.
     *
     * @return int
     */
    public function getBanDuration(): int
    {
        if (!isset($this->_globals['ban_duration'])) {
            $this->_globals['ban_duration'] = (int)$this->scopeConfig->getValue(self::XML_PATH_SIGNALS_BAN_DURATION);
        }

        return (int)$this->_globals['ban_duration'];
    }

    /**
     * Get cache technology config
     *
     * @return string
     */
    public function getCacheTechnology(): string
    {
        if (!isset($this->_globals['cache_technology'])) {
            $this->_globals['cache_technology'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_DECISIONS_CACHE_TECHNOLOGY
            );
        }

        return (string)$this->_globals['cache_technology'];
    }

    /**
     * Get cleaning events cron schedule expression config
     *
     * @return string
     */
    public function getCleanEventsCronExpr(): string
    {
        if (!isset($this->_globals['clean_events_expr'])) {
            $this->_globals['clean_events_expr'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_CRON_CLEAN_EVENTS_EXPR
            );
        }

        return (string)$this->_globals['clean_events_expr'];
    }

    /**
     * Get environment config.
     *
     * @return string
     */
    public function getEnv(): string
    {
        if (!isset($this->_globals['env'])) {
            $this->_globals['env'] = (string)$this->scopeConfig->getValue(self::XML_PATH_ENV);
        }

        return (string)$this->_globals['env'];
    }

    /**
     * Get event_lifetime config.
     *
     * @return int
     */
    public function getEventLifetime(): int
    {
        if (!isset($this->_globals['event_lifetime'])) {
            $this->_globals['event_lifetime'] = (int)$this->scopeConfig->getValue(self::XML_PATH_EVENT_LIFETIME);
        }

        return (int)$this->_globals['event_lifetime'];
    }

    /**
     * Get log level config.
     *
     * @return int
     */
    public function getLogLevel(): int
    {
        if (!isset($this->_globals['log_level'])) {
            $this->_globals['log_level'] = (int)$this->scopeConfig->getValue(self::XML_PATH_LOG_LEVEL);
        }

        return (int)$this->_globals['log_level'];
    }

    /**
     * Get Memcached DSN config
     *
     * @return string
     */
    public function getMemcachedDSN(): string
    {
        if (!isset($this->_globals['memcached_dsn'])) {
            $this->_globals['memcached_dsn'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_DECISIONS_CACHE_MEMCACHED_DSN
            );
        }

        return (string)$this->_globals['memcached_dsn'];
    }

    /**
     * Get pruning cron schedule expression config
     *
     * @return string
     */
    public function getPruneCronExpr(): string
    {
        if (!isset($this->_globals['prune_cache_expr'])) {
            $this->_globals['prune_cache_expr'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_CRON_PRUNE_CACHE_EXPR
            );
        }

        return (string)$this->_globals['prune_cache_expr'];
    }

    /**
     * Get pushing signal cron schedule expression config
     *
     * @return string
     */
    public function getPushSignalsCronExpr(): string
    {
        if (!isset($this->_globals['push_signals_expr'])) {
            $this->_globals['push_signals_expr'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_CRON_PUSH_SIGNALS_EXPR
            );
        }

        return (string)$this->_globals['push_signals_expr'];
    }

    /**
     * Get Redis DSN config
     *
     * @return string
     */
    public function getRedisDSN(): string
    {
        if (!isset($this->_globals['redis_dsn'])) {
            $this->_globals['redis_dsn'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_DECISIONS_CACHE_REDIS_DSN
            );
        }

        return (string)$this->_globals['redis_dsn'];
    }

    /**
     * Get refresh cron schedule expression config
     *
     * @return string
     */
    public function getRefreshCronExpr(): string
    {
        if (!isset($this->_globals['refresh_cache_expr'])) {
            $this->_globals['refresh_cache_expr'] = (string)$this->scopeConfig->getValue(
                self::XML_PATH_CRON_REFRESH_CACHE_EXPR
            );
        }

        return (string)$this->_globals['refresh_cache_expr'];
    }

    /**
     * Get signal scenarios config
     *
     * @return array
     */
    public function getSignalScenarios(): array
    {
        if (!isset($this->_globals['signal_scenarios'])) {
            $signalScenarios = $this->scopeConfig->getValue(self::XML_PATH_SIGNAL_SCENARIOS);

            $this->_globals['signal_scenarios'] =
                !empty($signalScenarios) ? explode(',', $signalScenarios) : [];
        }

        return (array)$this->_globals['signal_scenarios'];
    }

    /**
     * Get subscribed scenarios config
     *
     * @return array
     */
    public function getSubscribedScenarios(): array
    {
        if (!isset($this->_globals['subscribed_scenarios'])) {
            $subscribedScenarios = $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIBED_SCENARIOS);

            $this->_globals['subscribed_scenarios'] =
                !empty($subscribedScenarios) ? explode(',', $subscribedScenarios) : [];
        }

        return (array)$this->_globals['subscribed_scenarios'];
    }

    /**
     * Check if a scenario is set in config.
     *
     * @param string $name
     * @return bool
     */
    public function isScenarioEnabled(string $name): bool
    {
        if (!isset($this->_globals['scenario_enabled'][$name])) {
            $activeScenarios = $this->getSignalScenarios();

            $this->_globals['scenario_enabled'][$name] = in_array($name, $activeScenarios);
        }

        return $this->_globals['scenario_enabled'][$name];
    }

    /**
     * Get "ban locally" config
     *
     * @return bool
     */
    public function shouldBanLocally(): bool
    {
        if (!isset($this->_globals['ban_locally'])) {

            $this->_globals['ban_locally'] = (bool)$this->scopeConfig->getValue(self::XML_PATH_DECISIONS_BAN_LOCALLY);
        }

        return $this->_globals['ban_locally'];
    }

    /**
     * Get "bounce banned" config
     *
     * @return bool
     */
    public function shouldBounceBan(): bool
    {
        if (!isset($this->_globals['bounce_ban'])) {

            $this->_globals['bounce_ban'] = (bool)$this->scopeConfig->getValue(self::XML_PATH_DECISIONS_BOUNCE_BAN);
        }

        return $this->_globals['bounce_ban'];
    }
}
