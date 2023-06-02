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

namespace CrowdSec\Engine\Client;

use CrowdSec\CapiClient\WatcherFactory;
use CrowdSec\CapiClient\Watcher as CapiClient;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Constants;

class Watcher
{
    /**
     * @var Storage
     */
    private $_storage;
    /**
     * @var WatcherFactory
     */
    private $_watcherfactory;
    /**
     * @var CapiClient
     */
    private $_watcher;
    /**
     * @var array
     */
    private $_configs;
    /**
     * @var Helper
     */
    private $_helper;

    public function __construct(Storage $storage, WatcherFactory $watcherFactory, Helper $helper)
    {
        $this->_helper = $helper;

        // @TODO : retrieve configs
        $this->_configs = [
            'env' => $this->_helper->getEnv(),
            'api_timeout' => $this->_helper->getApiTimeout(),
            'scenarios' => $this->_helper->getSubscribedScenarios(),
            'user_agent_suffix' => Constants::USER_AGENT_SUFFIX,
            'user_agent_version' => Constants::VERSION,
        ];

        $this->_storage = $storage;
        $this->_watcherfactory = $watcherFactory;
    }

    public function init(): CapiClient
    {
        if (!isset($this->_watcher)) {
            $this->_watcher = $this->_watcherfactory->create(
                [
                    'configs' => $this->_configs,
                    'storage' => $this->_storage,
                    'capiHandler' => null,//cURL
                    'logger' => $this->_helper->getLogger()
                ]
            );
        }

        return $this->_watcher;
    }

}
