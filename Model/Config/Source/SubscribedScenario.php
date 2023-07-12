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
use Magento\Framework\DataObjectFactory;

class SubscribedScenario implements OptionSourceInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;
    /**
     * @var SignalScenarioFactory
     */
    private $signalScenarioFactory;

    /**
     * Constructor.
     *
     * @param ManagerInterface $eventManager
     * @param DataObjectFactory $dataObjectFactory
     * @param SignalScenarioFactory $signalScenarioFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory,
        SignalScenarioFactory $signalScenarioFactory
    ) {
        $this->eventManager = $eventManager;
        $this->signalScenarioFactory = $signalScenarioFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $signalsScenarios = $this->signalScenarioFactory->create();

        $list = $this->dataObjectFactory->create(['data' => [
            'crowdsecurity/http-backdoors-attempts' => __('Detect attempt to common backdoors'),
            'crowdsecurity/http-bad-user-agent' => __('Detect bad user-agents'),
            'crowdsecurity/http-crawl-non_statics' => __('Detect aggressive crawl from single ip'),
            'crowdsecurity/http-probing' => __('Detect site scanning/probing from a single ip'),
            'crowdsecurity/http-path-traversal-probing' => __('Detect path traversal attempt'),
            'crowdsecurity/http-sensitive-files' =>
                __('Detect attempt to access to sensitive files (.log, .db ..) or folders (.git)'),
            'crowdsecurity/http-sqli-probing' => __('Detect SQL injection probing with minimal false positives'),
            'crowdsecurity/http-xss-probing' => __('Detect XSS probing with minimal false positives'),
            'crowdsecurity/http-w00tw00t' => __('Detect w00tw00t'),
            'crowdsecurity/http-generic-bf' => __('Detect generic http brute force'),
            'crowdsecurity/http-open-proxy' => __('Detect scan for open proxy'),
            'crowdsecurity/http-logs' => __('Parse more Specifically HTTP logs'),
            'crowdsecurity/http-cve-2021-41773' => __('Detect cve-2021-41773'),
            'crowdsecurity/http-cve-2021-42013' => __('Detect cve-2021-42013 '),
            'crowdsecurity/grafana-cve-2021-43798' => __('Detect cve-2021-43798 exploitation attempts'),
            'crowdsecurity/vmware-vcenter-vmsa-2021-0027' =>
                __('Detect VMSA-2021-0027 exploitation attempts'),
            'crowdsecurity/pulse-secure-sslvpn-cve-2019-11510' => __('Detect cve-2019-11510 exploitation attempts'),
            'crowdsecurity/fortinet-cve-2018-13379' => __('Detect cve-2018-13379 exploitation attempts'),
            'crowdsecurity/f5-big-ip-cve-2020-5902' => __('Detect cve-2020-5902 exploitation attempts'),
            'crowdsecurity/thinkphp-cve-2018-20062' => __('Detect ThinkPHP CVE-2018-20062 exploitation attempts'),
            'crowdsecurity/apache_log4j2_cve-2021-44228' => __('Detect cve-2021-44228 exploitation attempts'),
            'crowdsecurity/jira_cve-2021-26086' => __('Detect Atlassian Jira CVE-2021-26086 exploitation attempts'),
            'crowdsecurity/spring4shell_cve-2022-22965' => __('Detect cve-2022-22965 probing'),
            'crowdsecurity/vmware-cve-2022-22954' => __('Detect Vmware CVE-2022-22954 exploitation attempts'),
            'crowdsecurity/CVE-2022-37042' => __('Detect CVE-2022-37042 exploits'),
            'crowdsecurity/CVE-2022-41082' => __('Detect CVE-2022-41082 exploits'),
            'crowdsecurity/CVE-2022-35914' => __('Detect CVE-2022-35914 exploits'),
            'crowdsecurity/CVE-2022-40684' => __('Detect cve-2022-40684 exploitation attempts'),
            'crowdsecurity/CVE-2022-26134' => __('Detect CVE-2022-26134 exploits '),
            'crowdsecurity/CVE-2022-42889' => __('Detect CVE-2022-42889 exploits (Text4Shell)'),
            'crowdsecurity/CVE-2022-41697' => __('Detect CVE-2022-41697 enumeration'),
            'crowdsecurity/CVE-2022-46169' => __('Detect CVE-2022-46169 brute forcing'),
            'crowdsecurity/CVE-2022-44877' => __('Detect CVE-2022-44877 exploits'),
            'crowdsecurity/CVE-2019-18935' => __('Detect Telerik CVE-2019-18935 exploitation attempts'),
            'crowdsecurity/netgear_rce' => __('Detect Netgear RCE DGN1000/DGN220 exploitation attempts'),
            'drupal/core-ban' => __('Drupal bans from administrators'),
            'drupal/auth-bruteforce' => __('Drupal bans from flood control'),
            'drupal/4xx-scan' => __('Drupal bans from whispers'),
            'shield/btinvalidscript' => __('Detect attempts to access scripts other than index.php'),
            'shield/btauthorfishing' => __('Detect attempts to find existing usernames by bruteforce'),
            'shield/ratelimit' => __('Detect users who get rate limited'),
            'shield/humanspam' => __('Detect users who spam wordpress comments'),
            'shield/markspam' => __('Detect a variety of bot behaviors using captchas'),
            'shield/btxml' => __('Detect attempts to access the XML RCP endpoint')
        ]]);

        // Allow other modules to add more scenarios.
        $this->eventManager->dispatch('crowdsec_engine_subscribed_scenarios', ['list' => $list]);

        $result = [];
        $i = 0;
        $scenarios = $list->toArray();
        foreach ($scenarios as $code => $description) {
            $result[$i]['value'] = $code;
            $result[$i]['label'] = $description;
            $i++;
        }

        $result = array_merge($result, $signalsScenarios->toOptionArray());
        usort($result, function ($a, $b) {
            return strcmp(strtolower($a['label']->getText()), strtolower($b['label']->getText()));
        });

        return $result;
    }
}
