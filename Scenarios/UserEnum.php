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
     * {@inheritdoc}
     */
    protected $description = 'Detect admin user enumeration';
    /**
     * @var int
     */
    protected $leakSpeed = 30;
    /**
     * {@inheritdoc}
     */
    protected $name = 'magento2/user-enum';
    /**
     * @var int
     */
    private $enumThreshold = 20;

    public function process(string $username): bool
    {
        $ip = $this->helper->getRealIp();

        $event = $this->eventHelper->getLastEvent($ip, $this->getName());

        if ($this->createFreshEvent($event, $ip, ['enum' => [$username], 'duration' => $this->helper->getBanDuration()])) {
            return true;
        }
        $context = $event->getContext();
        if(isset($context['enum']) && !in_array($username, $context['enum'])){
            $context['enum'][] = $username;
        }

        return $this->updateEvent($event, array_merge($context, ['duration' => $this->hlper->getBanDuration()]));
    }

    /**
     * If there is a saved created event, we pass through the leaking bucket mechanism
     * We also look for the enumeration threshold
     * Returns true if event is updated
     *
     * @param EventInterface $event
     * @return bool
     */
    protected function updateEvent(EventInterface $event, array $context = []): bool
    {
        if ($event->getStatusId() === EventInterface::STATUS_CREATED) {
            $count = $this->getLeakingBucketCount($event)+1;
            $enumCount = isset($context['enum']) ? count($context['enum']) : 0;

            $alertTriggered = false;
            if ($count > $this->getBucketCapacity() || $enumCount > $this->enumThreshold) {
                // Threshold reached, take actions.
                $event->setStatusId(EventInterface::STATUS_ALERT_TRIGGERED);
                $alertTriggered = true;
            }
            $this->saveEvent($event->setCount($count), $context);
            if ($alertTriggered) {
                // This event gives possibility to take actions when alert is triggered (ban locally, etc...)
                $eventParams = ['alert_event' => $event];
                $this->eventManager->dispatch('crowdsec_engine_alert_triggered', $eventParams);
            }

            return true;
        }

        return false;
    }

}
