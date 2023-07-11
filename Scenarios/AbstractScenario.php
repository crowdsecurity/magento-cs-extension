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
    protected $bucketCapacity = 10;
    /**
     * @var string
     */
    protected $description = '';
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
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var int
     */
    protected $leakSpeed = 10;
    /**
     * @var string
     */
    protected $name = '';

    /**
     * Constructor.
     *
     * @param Helper $helper
     * @param EventRepositoryInterface $eventRepository
     * @param EventHelper $eventHelper
     * @param EventFactory $eventFactory
     * @param Manager $manager
     */
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

    /**
     * "blackHole" getter.
     *
     * @return int
     */
    public function getBlackHole(): int
    {
        return $this->blackHole;
    }

    /**
     * "bucketCapacity" getter
     *
     * @return int
     */
    public function getBucketCapacity(): int
    {
        return $this->bucketCapacity;
    }

    /**
     * "description" getter
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * "leakSpeed" getter.
     *
     * @return int
     */
    public function getLeakSpeed(): int
    {
        return $this->leakSpeed;
    }

    /**
     * "name" getter
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Leaking bucket count strategy.
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
     * Save event.
     *
     * @param EventInterface $event
     * @return EventInterface
     * @throws LocalizedException
     */
    protected function saveEvent(EventInterface $event): EventInterface
    {
        $event->setLastEventDate($this->helper->getCurrentGMTDate());
        if ($this->shouldTriggerAlert($event)) {
            // Threshold reached, take actions.
            $event->setStatusId(EventInterface::STATUS_ALERT_TRIGGERED);
        }

        return $this->eventRepository->save($event);
    }

    /**
     * Determines if an alert should be triggered
     *
     * @param EventInterface $event
     * @return bool
     */
    protected function shouldTriggerAlert(EventInterface $event): bool
    {
        return $event->getCount() > $this->getBucketCapacity();
    }

    /**
     * Create or update an event.
     *
     * If event is not saved or is a non black-holed sent or triggered event, we save a fresh one
     * If there is a saved created event, we pass through the leaking bucket mechanism
     *
     * Returns true if event is saved
     *
     * @param EventInterface $event
     * @return bool
     * @throws LocalizedException
     */
    protected function upsert(EventInterface $event): bool
    {
        $saved = false;
        if (!$event->getId()) {
            $event->setCount(1);
            $this->saveEvent($event);

            $saved = true;
        } elseif (in_array($event->getStatusId(), [EventInterface::STATUS_ALERT_TRIGGERED,
                EventInterface::STATUS_SIGNAL_PUSHED]) && !$this->isBlackHoleFor($event)) {
            $freshEvent = $this->eventFactory->create();
            $freshEvent->setCount(1)->setIp($event->getIp())->setScenario($event->getScenario());
            $this->saveEvent($freshEvent);

            $saved = true;
        } elseif ($event->getStatusId() === EventInterface::STATUS_CREATED) {
            $event->setCount($this->getLeakingBucketCount($event) + 1);

            $this->saveEvent($event);

            $saved = true;
        }

        return $saved;
    }

    /**
     * An event is in "black hole" when the last event is too recent.
     *
     * @param EventInterface $event
     * @return bool
     */
    private function isBlackHoleFor(EventInterface $event): bool
    {
        return $this->eventHelper->isInBlackHole(time(), $event, $this->getBlackHole());
    }
}
