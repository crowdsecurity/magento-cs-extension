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

use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\CapiEngine\Remediation;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use CrowdSec\Engine\Constants;

class BounceIp implements ObserverInterface
{

    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var Remediation
     */
    private $remediation;


    public function __construct(
        Helper $helper,
        Remediation $remediation
    ) {
        $this->helper = $helper;
        $this->remediation = $remediation;
    }

    public function execute(Observer $observer): BounceIp
    {
        if(!$this->helper->shouldBounceBan()){
            return $this;
        }

        //@TODO try catch log error

        $ip = $this->helper->getRealIp();
        $remediation = $this->remediation->getIpRemediation($ip);

        if($remediation === Constants::REMEDIATION_BAN){
            /**
             * @var $response Response
             */
            $response = $observer->getEvent()->getResponse();
            $response->setNoCacheHeaders();
            $response->setBody('<h1>IP banned by CrowdSec</h1>')->setStatusCode(Http::STATUS_CODE_403);
        }

        return $this;
    }
}
