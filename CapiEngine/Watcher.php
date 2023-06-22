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

use CrowdSec\CapiClient\Watcher as CapiClient;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Constants;

class Watcher extends CapiClient
{
    /**
     * @var Helper
     */
    private $helper;

    public function __construct(Storage $storage, Helper $helper)
    {
        $this->helper = $helper;

        $configs = [
            'env' => $this->helper->getEnv(),
            'api_timeout' => $this->helper->getApiTimeout(),
            'scenarios' => $this->helper->getSubscribedScenarios(),
            'user_agent_suffix' => Constants::USER_AGENT_SUFFIX,
            'user_agent_version' => Constants::VERSION,
        ];

        parent::__construct($configs, $storage, null, $this->helper->getLogger());
    }
}
