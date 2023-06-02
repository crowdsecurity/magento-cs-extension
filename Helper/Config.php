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

    // General configs
    public const XML_PATH_ENV = self::SECTION . '/general/environment';
    public const XML_PATH_LOG_LEVEL = self::SECTION . '/general/log_level';
    public const XML_PATH_API_TIMEOUT = self::SECTION . '/general/api_timeout';

    // Subscribed scenario
    public const XML_PATH_SUBSCRIBED_SCENARIOS = self::SECTION . '/subscribed_scenarios/list';


    protected $_globals = [
        'api_timeout' => null,
        'env' => null,
        'log_level' => null,
        'subscribed_scenarios' => null
    ];

    /**
     * Data constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context       $context,
    ) {
        parent::__construct($context);
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
     * @throws InvalidArgumentException
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
