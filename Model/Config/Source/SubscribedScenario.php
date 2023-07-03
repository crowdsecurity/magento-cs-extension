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

namespace CrowdSec\Engine\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Event\ManagerInterface;

class SubscribedScenario implements OptionSourceInterface
{

    protected $_eventManager;

    public function __construct(ManagerInterface $eventManager)
    {
        $this->_eventManager = $eventManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {

        $list = new \ArrayObject([
            'crowdsecurity/http-backdoors-attempts' => __('Detect attempt to common backdoors'),
            'crowdsecurity/http-bad-user-agent' => __('Detect bad user-agents'),
            'crowdsecurity/http-crawl-non_statics' => __('Detect aggressive crawl from single ip'),
            'crowdsecurity/http-probing' => __('Detect site scanning/probing from a single ip'),
            'crowdsecurity/http-path-traversal-probing' => __('Detect path traversal attempt'),
            'crowdsecurity/http-sensitive-files' => __('Detect attempt to access to sensitive files (.log, .db ..) or folders (.git)'),
            'crowdsecurity/http-sqli-probing' => __('Detect SQL injection probing with minimal false positives'),
            'crowdsecurity/http-xss-probing' => __('Detect XSS probing with minimal false positives'),
            'crowdsecurity/http-w00tw00t' => __('Detect w00tw00t'),
            'crowdsecurity/http-generic-bf' => __('Detect generic http brute force'),
            'crowdsecurity/http-open-proxy' => __('Detect scan for open proxy'),
        ]);

        // Allow other modules to add more scenarios.
        $this->_eventManager->dispatch('crowdsec_engine_subscribed_scenarios', ['list' => $list]);

        $result = [];
        $i = 0;
        $scenarios = $list->getArrayCopy();
        foreach ($scenarios as $code => $description) {
            $result[$i]['value'] = $code;
            $result[$i]['label'] = $description;
            $i++;
        }

        return $result;
    }
}
