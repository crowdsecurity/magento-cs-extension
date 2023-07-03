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

namespace CrowdSec\Engine\Observer;

use CrowdSec\RemediationEngine\Decision;
use CrowdSec\RemediationEngine\DecisionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\CapiEngine\Remediation;
use CrowdSec\Engine\Constants;
use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Scenarios\AbstractScenario;

class BanLocally implements ObserverInterface
{

    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var Remediation
     */
    private $remediation;
    /**
     * @var DecisionFactory
     */
    private $decisionFactory;


    public function __construct(
        Helper $helper,
        Remediation $remediation,
        DecisionFactory $decisionFactory
    ) {
        $this->helper = $helper;
        $this->remediation = $remediation;
        $this->decisionFactory = $decisionFactory;
    }

    public function execute(Observer $observer): BanLocally
    {
        if(!$this->helper->shouldBanLocally()){
            return $this;
        }

        //@TODO try catch log error

        /**
         * @var $event EventInterface
         */
        $event = $observer->getEvent()->getAlertEvent();

        $ip = $event->getIp();
        $origin = Constants::ORIGIN;
        $type = Constants::REMEDIATION_BAN;
        $scope = Constants::SCOPE_IP;
        $value = $ip;
        $decision = $this->decisionFactory->create([
            'identifier' => $origin . Decision::ID_SEP . $type . Decision::ID_SEP .
                            $scope . Decision::ID_SEP . $value, 'scope' => $scope,
            'value' => $value,
            'type' => $type,
            'origin' => $origin,
            'expiresAt' => time() + $this->helper->getBanDuration()]);

        $this->remediation->getCacheStorage()->storeDecision($decision);


        return $this;
    }
}
