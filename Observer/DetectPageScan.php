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

use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Helper\Event as EventHelper;
use CrowdSec\Engine\Model\EventFactory;
use CrowdSec\Engine\Scenarios\PagesScan;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DetectPageScan implements ObserverInterface
{

    /**
     * @var EventFactory
     */
    private $eventFactory;
    /**
     * @var EventHelper
     */
    private $eventHelper;
    /**
     * @var Manager
     */
    private $eventManager;
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var PagesScan
     */
    private $scenario;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventFactory $eventFactory,
        Helper $helper,
        EventHelper $eventHelper,
        PagesScan $scenario,
        Manager $manager
    ) {
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->helper = $helper;
        $this->eventHelper = $eventHelper;
        $this->scenario = $scenario;
        $this->eventManager = $manager;
    }

    public function execute(Observer $observer): DetectPageScan
    {
        $scenarioName = $this->scenario->getName();
        if (!$this->helper->isScenarioEnabled($scenarioName)) {
            return $this;
        }

        /**
         * @var $response \Magento\Framework\HTTP\PhpEnvironment\Response
         */
        $response = $observer->getEvent()->getResponse();

        if (in_array($response->getStatusCode(), $this->scenario->getDetectedScans())) {
            $ip = $this->helper->getRealIp();

            $event = $this->eventHelper->getLastEvent($ip, $scenarioName);
            $status = $event->getStatusId();

            $saveFlag = false;

            /**
             * If there is no saved event or if the saved event is a non black-holed sent or triggered event,
             * we create and save a fresh one
             */
            if (
                !$event->getId() ||
                ($event->getId() &&
                    in_array($status, [EventInterface::STATUS_ALERT_TRIGGERED, EventInterface::STATUS_SIGNAL_SENT]) &&
                    !$this->scenario->isBlackHoleFor($event))
            ) {
                $event = $this->eventFactory->create();
                $event->setIp($ip)
                    ->setScenario($scenarioName)
                    ->setCount(1);
                $saveFlag = true;
            }

            /**
             * If there is a saved created event, we pass through the leaking bucket mechanism
             */
            if ($event->getId() && $status === EventInterface::STATUS_CREATED) {
                $event->setCount($this->scenario->getLeakingBucketCount($event));
                if ($event->getCount() > $this->scenario->getBucketCapacity()) {
                    // Threshold reached, take actions.
                    $event->setStatusId(EventInterface::STATUS_ALERT_TRIGGERED);
                    // This event gives possibility to take actions when alert is triggered (ban locally, etc...)
                    $eventParams = ['event' => $event, 'scenario' => $this->scenario];
                    $this->_eventManager->dispatch('crowdsec_engine_alert_triggered', $eventParams);
                }
                $saveFlag = true;
            }

            if ($saveFlag) {
                $context = array_merge($event->getContext(), ['duration' => $this->scenario->getDuration()]);
                $event->setLastEventDate($this->helper->getCurrentGMTDate())->setContext($context);
                $this->eventRepository->save($event);
            }
        }

        return $this;
    }
}
