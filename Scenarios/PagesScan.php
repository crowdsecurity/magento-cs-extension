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

namespace CrowdSec\Engine\Scenarios;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\Response;

class PagesScan extends AbstractScenario
{
    /**
     * @var string
     */
    protected $description = 'Detect pages scan';

    /**
     * @var string
     */
    protected $name = 'magento2/pages-scan';

    /**
     * @var array
     */
    protected $detectedScans = [HttpResponse::STATUS_CODE_404, HttpResponse::STATUS_CODE_403];

    /**
     * Manage events for page scan scenario.
     *
     * @param Response $response
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     */
    public function process(Response $response): bool
    {
        if (in_array($response->getStatusCode(), $this->detectedScans)) {
            $ip = $this->helper->getRealIp();
            $scenarioName = $this->getName();
            $event = $this->eventHelper->getLastEvent($ip, $scenarioName);
            $context = ['duration' => $this->helper->getBanDuration()];
            $this->helper->getLogger()->debug(
                'Detected event',
                [
                    'ip' => $ip,
                    'scenario' => $scenarioName
                ]
            );

            if ($this->createFreshEvent($event, $ip, $context)) {
                return true;
            }

            return $this->updateEvent($event, $context);
        }

        return false;
    }
}
