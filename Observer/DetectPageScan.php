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

use CrowdSec\Engine\Constants;
use CrowdSec\Engine\Model\EventFactory;
use CrowdSec\RemediationEngine\Decision;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderFactory;
use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Scenarios\PagesScan;
use CrowdSec\Engine\CapiEngine\Remediation;
use CrowdSec\RemediationEngine\DecisionFactory;

class DetectPageScan implements ObserverInterface
{
    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var EventInterface
     */
    private $event;
    /**
     * @var EventFactory
     */
    private $eventFactory;
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var Helper
     */
    private $_helper;
    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;
    /**
     * @var PagesScan
     */
    private $scenario;
    /**
     * @var SortOrderFactory
     */
    private $sortOrderFactory;

    /**
     * @var Remediation
     */
    private $remediation;
    /**
     * @var DecisionFactory
     */
    private $decisionFactory;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventInterface $event,
        EventFactory $eventFactory,
        Helper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderFactory $sortOrderFactory,
        ActionFlag $actionFlag,
        PagesScan $scenario,
        Remediation $remediation,
        DecisionFactory $decisionFactory
    ) {
        $this->event = $event;
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->_helper = $helper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderFactory = $sortOrderFactory;
        $this->_actionFlag = $actionFlag;
        $this->scenario = $scenario;
        $this->remediation = $remediation;
        $this->decisionFactory = $decisionFactory;
    }

    public function execute(Observer $observer): DetectPageScan
    {
        $scenarioName = $this->scenario->getName();
        if (!$this->_helper->isScenarioEnabled($scenarioName)) {
            return $this;
        }

        /**
         * @var $response \Magento\Framework\HTTP\PhpEnvironment\Response
         */
        $response = $observer->getEvent()->getResponse();

        if (in_array($response->getStatusCode(), $this->scenario->getDetectedScans())) {
            $ip = $this->_helper->getRealIp();
            $sort = $this->sortOrderFactory->create()
                ->setField(EventInterface::LAST_EVENT_DATE)
                ->setDirection(SortOrder::SORT_DESC);

            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(EventInterface::SCENARIO, $scenarioName)
                ->addFilter(EventInterface::IP, $ip)
                ->setPageSize(1)
                ->setCurrentPage(1)
                ->setSortOrders([$sort])
                ->create();

            $events = $this->eventRepository->getList($searchCriteria);
            $firstItem = current($events->getItems());

            $this->event = $firstItem ?: $this->event;

            $saveFlag = false;

            // Case 1: no event in database
            if (!$this->event->getId()) {
                $this->event->setCount(1);
                $saveFlag = true;
            }

            $status = $this->event->getStatusId();
            // Case 2: a created event in database
            if ($this->event->getId() && $status === EventInterface::STATUS_CREATED) {
                // Leaking bucket implementation
                $this->event->setCount($this->scenario->getLeakingBucketCount($this->event));
                if ($this->event->getCount() > $this->scenario->getBucketCapacity()) {
                    // Threshold reached, take actions.
                    $this->event->setStatusId(EventInterface::STATUS_ALERT_TRIGGERED);
                    // @TODO check config before ban
                    $origin = Constants::ORIGIN;
                    $type = Constants::REMEDIATION_BAN;
                    $scope = Constants::SCOPE_IP;
                    $value = $ip;
                    $decision = $this->decisionFactory->create([
                        'identifier' => $origin . Decision::ID_SEP . $type . Decision::ID_SEP .
                                        $scope . Decision::ID_SEP . $value, 'scope' => $scope,
                        'value' => $value,
                        'type' => $type,
                        'origin' => $origin,
                        'expiresAt' => time() + $this->scenario->getDuration()]);

                    $this->remediation->getCacheStorage()->storeDecision($decision);
                }
                $saveFlag = true;
            }
            // Case 3: a non black-holed sent or triggered event
            if (
                $this->event->getId() &&
                in_array($status, [EventInterface::STATUS_ALERT_TRIGGERED, EventInterface::STATUS_SIGNAL_SENT]) &&
                !$this->scenario->isBlackHoleFor($this->event)
            ) {
                $this->event = $this->eventFactory->create();
                $this->event->setCount(1);
                $saveFlag = true;
            }

            if ($saveFlag) {
                $this->event->setIp($ip)
                    ->setScenario($scenarioName)
                    ->setLastEventDate($this->_helper->getCurrentGMTDate());
                $this->eventRepository->save($this->event);
            }
        }

        return $this;
    }
}
