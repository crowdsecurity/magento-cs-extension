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

use CrowdSec\Engine\Api\Data\EventInterface;

class UserEnum extends AbstractScenario
{
    /**
     * @var string
     */
    protected $description = 'Detect admin user enumeration';
    /**
     * @var int
     */
    protected $leakSpeed = 30;
    /**
     * @var string
     */
    protected $name = 'magento2/user-enum';
    /**
     * @var int
     */
    private $enumThreshold = 20;

    /**
     * Manage events for user enum scenario.
     *
     * @param string $username
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(string $username): bool
    {
        $ip = $this->helper->getRealIp();
        $scenarioName = $this->getName();
        $event = $this->eventHelper->getLastEvent($ip, $scenarioName);
        $context = $event->getContext();
        if (!isset($context['enum'])) {
            $context['enum'] = [$username];
        } elseif (!in_array($username, $context['enum'])) {
            $context['enum'][] = $username;
        }

        $event->setContext(array_merge($context, ['duration' => $this->helper->getBanDuration()]));

        if ($this->upsert($event)) {
            $this->helper->getLogger()->debug(
                'Detected event saved',
                [
                    'ip' => $ip,
                    'scenario' => $scenarioName,
                    'context' => $event->getContext()
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function shouldTriggerAlert(EventInterface $event): bool
    {
        $context = $event->getContext();
        $enumCount = isset($context['enum']) ? count($context['enum']) : 0;

        return $event->getCount() > $this->getBucketCapacity() || $enumCount > $this->enumThreshold;
    }
}
