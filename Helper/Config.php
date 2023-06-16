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

    // Signal scenarios
    // Codes must be named as in etc/adminhtml/system.xml (see signal_scenarios subgroups ids)
    public const ADMIN_AUTH_FAILED_CODE = 'admin_auth_failed';
    public const SCAN_4XX_CODE = 'scan_4xx';

    // Subscribed scenarios
    public const XML_PATH_SUBSCRIBED_SCENARIOS = self::SECTION . '/subscribed_scenarios/list';


    protected array $_globals = [
        'api_timeout' => null,
        'env' => null,
        'log_level' => null,
        'scenario_enabled' => [],
        'scenario_rules' => [],
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

    /**
     * Get scenario rule config for some scenario code
     *
     * @param string $code
     * @return array
     */
    public function getScenarioRule(string $code): array
    {
        if (!isset($this->_globals['scenario_rules'][$code])) {

            $this->_globals['scenario_rules'][$code] = [
                'enabled' => (bool)$this->scopeConfig->getValue(self::SIGNAL_SCENARIOS . '/' . $code . '/enabled'),
                'time_period' => (int)$this->scopeConfig->getValue(self::SIGNAL_SCENARIOS . '/' . $code . '/time_period'),
                'threshold' => (int)$this->scopeConfig->getValue(self::SIGNAL_SCENARIOS . '/' . $code . '/threshold'),
                'duration' => (int)$this->scopeConfig->getValue(self::SIGNAL_SCENARIOS . '/' . $code . '/duration')
            ];
        }

        return $this->_globals['scenario_rules'][$code];
    }

    public function isScenarioEnabled(string $code): bool
    {
        if (!isset($this->_globals['scenario_enabled'][$code])) {
            $rule = $this->getScenarioRule($code);

            $this->_globals['scenario_enabled'][$code] = !empty($rule['enabled']);

        }

        return $this->_globals['scenario_enabled'][$code];

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
}
