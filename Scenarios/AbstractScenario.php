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
use CrowdSec\Engine\Api\EventRepositoryInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Helper\Event as EventHelper;
use CrowdSec\Engine\Model\EventFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractScenario
{
    /**
     * @var int
     */
    protected $blackHole = EventInterface::BLACK_HOLE_DEFAULT;
    /**
     * @var int
     */
    protected $leakSpeed = 10;
    /**
     * @var int
     */
    protected $bucketCapacity = 10;
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var string
     */
    protected $name = '';
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    /**
     * @var Manager
     */
    protected $eventManager;

    public function __construct(
        Helper $helper,
        EventRepositoryInterface $eventRepository,
        EventHelper $eventHelper,
        EventFactory $eventFactory,
        Manager $manager

    ) {
        $this->eventHelper = $eventHelper;
        $this->eventFactory = $eventFactory;
        $this->eventManager = $manager;
        $this->helper = $helper;
        $this->eventRepository = $eventRepository;
    }

    public function getBlackHole(): int
    {
        return $this->blackHole;
    }

    public function getLeakSpeed(): int
    {
        return $this->leakSpeed;
    }

    public function getBucketCapacity(): int
    {
        return $this->bucketCapacity;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Leaking bucket count strategy
     *
     * @param EventInterface $event
     * @return int
     */
    protected function getLeakingBucketCount(EventInterface $event): int
    {
        return $this->eventHelper->getLeakingBucketCount(
            time(),
            $event->getCount(),
            (int)strtotime($event->getLastEventDate()),
            $this->getLeakSpeed()
        );
    }

    /**
     *
     * @param EventInterface $event
     * @return bool
     */
    private function isBlackHoleFor(EventInterface $event): bool
    {
        return $this->eventHelper->isInBlackHole(time(), $event, $this->getBlackHole());
    }

    /**
     * @throws LocalizedException
     */
    protected function saveEvent(EventInterface $event, array $context = []): EventInterface
    {
        $context = array_merge($event->getContext() ?? [], $context);
        $event->setLastEventDate($this->helper->getCurrentGMTDate())->setContext($context);

        return $this->eventRepository->save($event);
    }

    /**
     * If event is not saved or is a non black-holed sent or triggered event, we create and save a fresh one
     * Returns true if a fresh event is created
     *
     * @param EventInterface|null $event
     * @param string $ip
     * @param array $context
     * @return bool
     * @throws LocalizedException
     * @throws LocalizedException
     */
    protected function createFreshEvent(?EventInterface $event, string $ip, array $context = []): bool
    {
        if (
            !$event ||
            (in_array($event->getStatusId(), [EventInterface::STATUS_ALERT_TRIGGERED,
                 EventInterface::STATUS_SIGNAL_PUSHED])
             && !$this->isBlackHoleFor($event))
        ) {
            $event = $this->eventFactory->create();
            $event->setIp($ip)
                ->setScenario($this->getName())
                ->setCount(1);
            $this->saveEvent($event, $context);

            return true;
        }

        return false;
    }

    /**
     * If there is a saved created event, we pass through the leaking bucket mechanism
     * Returns true if event is updated
     *
     * @param EventInterface $event
     * @param array $context
     * @return bool
     * @throws LocalizedException
     */
    protected function updateEvent(EventInterface $event, array $context = []): bool
    {
        if ($event->getStatusId() === EventInterface::STATUS_CREATED) {
            $count = $this->getLeakingBucketCount($event)+1;
            $alertTriggered = false;
            if ($count > $this->getBucketCapacity()) {
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
