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

use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Scenarios\PagesScan;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;

class DetectPageScan implements ObserverInterface
{

    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var PagesScan
     */
    private $scenario;

    /**
     * Constructor.
     *
     * @param Helper $helper
     * @param PagesScan $scenario
     */
    public function __construct(
        Helper $helper,
        PagesScan $scenario
    ) {
        $this->helper = $helper;
        $this->scenario = $scenario;
    }

    /**
     * Handle page scan detection
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): DetectPageScan
    {
        try {
            $scenarioName = $this->scenario->getName();
            if (!$this->helper->isScenarioEnabled($scenarioName)) {
                return $this;
            }

            /**
             * @var $response Response
             */
            $response = $observer->getEvent()->getResponse();

            $this->scenario->process($response);
        } catch (\Exception $e) {
            $this->helper->getLogger()->error(
                'Technical error while detecting pages scan',
                ['message' => $e->getMessage()]
            );
        }

        return $this;
    }
}
