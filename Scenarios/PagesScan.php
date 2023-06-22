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
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Manager;
use Magento\Framework\HTTP\PhpEnvironment\Response;

class PagesScan extends AbstractScenario
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

    protected $description = 'Detect pages scan';

    protected $name = 'magento2/pages-scan';

    /**
     * @var array
     */
    protected $detectedScans = [HttpResponse::STATUS_CODE_404, HttpResponse::STATUS_CODE_403];

    public function __construct(
        Helper $helper,
        EventHelper $eventHelper,
        EventFactory $eventFactory,
        EventRepositoryInterface $eventRepository,
        Manager $manager

    ) {
        $this->helper = $helper;
        $this->eventHelper = $eventHelper;
        $this->eventFactory = $eventFactory;
        $this->eventManager = $manager;
        $this->eventRepository = $eventRepository;
    }

    public function process(Response $response): void
    {
        if (in_array($response->getStatusCode(), $this->detectedScans)) {
            $ip = $this->helper->getRealIp();

            $event = $this->eventHelper->getLastEvent($ip, $this->getName());
            $status = $event->getStatusId();

            /**
             * If there is no saved event or if the saved event is a non black-holed sent or triggered event,
             * we create and save a fresh one
             */
            if (
                !$event->getId() ||
                ($event->getId() &&
                 in_array($status, [EventInterface::STATUS_ALERT_TRIGGERED, EventInterface::STATUS_SIGNAL_SENT]) &&
                 !$this->isBlackHoleFor($event))
            ) {
                $event = $this->eventFactory->create();
                $event->setIp($ip)
                    ->setScenario($this->getName())
                    ->setCount(1);
                $this->saveEvent($event);

                return;
            }

            /**
             * If there is a saved created event, we pass through the leaking bucket mechanism
             */
            if ($event->getId() && $status === EventInterface::STATUS_CREATED) {
                $count = $this->getLeakingBucketCount($event);
                $alertTriggered = false;
                if ($count > $this->getBucketCapacity()) {
                    // Threshold reached, take actions.
                    $event->setStatusId(EventInterface::STATUS_ALERT_TRIGGERED);
                    $alertTriggered = true;
                }
                $this->saveEvent($event->setCount($count));
                if ($alertTriggered) {
                    // This event gives possibility to take actions when alert is triggered (ban locally, etc...)
                    $eventParams = ['alert_event' => $event, 'scenario' => $this];
                    $this->eventManager->dispatch('crowdsec_engine_alert_triggered', $eventParams);
                }
            }
        }
    }

    private function saveEvent(EventInterface $event): EventInterface
    {
        $context = array_merge($event->getContext() ?? [], ['duration' => $this->getDuration()]);
        $event->setLastEventDate($this->helper->getCurrentGMTDate())->setContext($context);

        return $this->eventRepository->save($event);
    }
}
