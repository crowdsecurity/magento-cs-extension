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

use CrowdSec\RemediationEngine\DecisionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CrowdSec\Engine\Helper\Event as Helper;

class HandleDetectedAlert implements ObserverInterface
{

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Handle detected alert (add alert to queue).
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): HandleDetectedAlert
    {
        try {
            $alert = $observer->getEvent()->getAlert();
            $this->helper->addAlertToQueue($alert);
        } catch (\Exception $e) {
            $this->helper->getLogger()->error(
                'Technical error while handling detected alert',
                ['message' => $e->getMessage()]
            );
        }

        return $this;
    }
}
