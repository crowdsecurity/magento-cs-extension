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
use Magento\Framework\App\Helper\Context;

class Config extends AbstractHelper
{

    private const SECTION = 'crowdsec_engine';

    private const SIGNAL_SCENARIOS = self::SECTION . '/signal_scenarios';

    // General configs
    public const XML_PATH_ENV = self::SECTION . '/general/environment';
    public const XML_PATH_LOG_LEVEL = self::SECTION . '/general/log_level';
    public const XML_PATH_API_TIMEOUT = self::SECTION . '/general/api_timeout';

    /**
     * Signal scenarios
     */
    public const XML_PATH_SIGNAL_SCENARIOS = self::SECTION . '/signal_scenarios/list';
    public const XML_PATH_BAN_LOCALLY = self::SECTION . '/signal_scenarios/ban_locally';

    /**
     * Bouncing
     */
    // Banned IP
    public const XML_PATH_BOUNCE_BAN = self::SECTION . '/decisions/bounce_ban';

    // Subscribed scenarios
    public const XML_PATH_SUBSCRIBED_SCENARIOS = self::SECTION . '/decisions/subscribed_scenarios';
    // Cache
    public const XML_PATH_BOUNCING_CACHE_TECHNOLOGY = self::SECTION . '/decisions/cache/technology';
    public const XML_PATH_BOUNCING_CACHE_REDIS_DSN = self::SECTION . '/decisions/cache/redis_dsn';
    public const XML_PATH_BOUNCING_CACHE_MEMCACHED_DSN = self::SECTION . '/decisions/cache/memcached_dsn';


    protected array $_globals = [
        'api_timeout' => null,
        'ban_locally' => null,
        'bounce_ban' => null,
        'cache_technology' => null,
        'env' => null,
        'log_level' => null,
        'memcached_dsn' => null,
        'redis_dsn' => null,
        'scenario_enabled' => [],
        'signal_scenarios' => null,
        'subscribed_scenarios' => null
    ];

    /**
     * Data constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context       $context
    ) {
        parent::__construct($context);
    }


    public function isScenarioEnabled(string $name): bool
    {
        if (!isset($this->_globals['scenario_enabled'][$name])) {
            $activeScenarios = $this->getSignalScenarios();

            $this->_globals['scenario_enabled'][$name] = in_array($name, $activeScenarios);
        }

        return $this->_globals['scenario_enabled'][$name];

    }

    /**
     * Get "bounce banned" config
     *
     * @return bool
     */
    public function shouldBounceBan(): bool
    {
        if (!isset($this->_globals['bounce_ban'])) {

            $this->_globals['bounce_ban'] = (bool)$this->scopeConfig->getValue(self::XML_PATH_BOUNCE_BAN);
        }

        return $this->_globals['bounce_ban'];

    }

    /**
     * Get "ban locally" config
     *
     * @return bool
     */
    public function shouldBanLocally(): bool
    {
        if (!isset($this->_globals['ban_locally'])) {

            $this->_globals['ban_locally'] = (bool)$this->scopeConfig->getValue(self::XML_PATH_BAN_LOCALLY);
        }

        return $this->_globals['ban_locally'];

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
                self::XML_PATH_BOUNCING_CACHE_TECHNOLOGY
            );
        }

        return (string)$this->_globals['cache_technology'];
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
                self::XML_PATH_BOUNCING_CACHE_REDIS_DSN
            );
        }

        return (string)$this->_globals['redis_dsn'];
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
                self::XML_PATH_BOUNCING_CACHE_MEMCACHED_DSN
            );
        }

        return (string)$this->_globals['memcached_dsn'];
    }

    /**
     * Get api timeout config
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
     * Get log level config
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
     * Get environment config
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
}
