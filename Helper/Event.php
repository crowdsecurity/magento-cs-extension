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

namespace CrowdSec\Engine\Helper;

use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderFactory;
use CrowdSec\Engine\Model\EventFactory;

class Event extends AbstractHelper
{
    /**
     * @var SortOrderFactory
     */
    private $sortOrderFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var EventFactory
     */
    private $eventFactory;
    /**
     * @var array
     */
    private $lastEvent = [];

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param EventFactory $eventFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderFactory $sortOrderFactory
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        EventFactory $eventFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderFactory $sortOrderFactory,
        EventRepositoryInterface $eventRepository
    ) {
        $this->eventFactory = $eventFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderFactory = $sortOrderFactory;
        $this->eventRepository = $eventRepository;

        parent::__construct($context);
    }

    /**
     * Retrieve last event for some ip and scenario.
     * If no result, return an empty event object
     *
     * @param string $ip
     * @param string $scenario
     * @return EventInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastEvent(string $ip, string $scenario): EventInterface
    {
        if (!isset($this->lastEvent[$ip][$scenario])) {
            $sort = $this->sortOrderFactory->create()
                ->setField(EventInterface::LAST_EVENT_DATE)
                ->setDirection(SortOrder::SORT_DESC);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(EventInterface::SCENARIO, $scenario)
                ->addFilter(EventInterface::IP, $ip)
                ->setPageSize(1)
                ->setCurrentPage(1)
                ->setSortOrders([$sort])
                ->create();

            $events = $this->eventRepository->getList($searchCriteria);
            $firstItem = current($events->getItems());

            $this->lastEvent[$ip][$scenario] = $firstItem ?: $this->eventFactory->create();
        }

        return $this->lastEvent[$ip][$scenario];
    }

}
