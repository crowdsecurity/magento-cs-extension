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

namespace CrowdSec\Engine\Cron;

use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CleanEvents
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param Helper $helper
     * @param EventRepositoryInterface $eventRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Helper $helper,
        EventRepositoryInterface $eventRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->helper = $helper;
        $this->eventRepository = $eventRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Clean old events
     *
     * @return void
     */
    public function execute(): int
    {

        $result = 0;
        try {
            $lifetime = $this->helper->getEventLifetime();

            $threshold = date('Y-m-d h:i:s', strtotime("-$lifetime day"));

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(EventInterface::LAST_EVENT_DATE, $threshold, 'lteq')
                ->create();

            $events = $this->eventRepository->getList($searchCriteria)->getItems();

            $allIds = array_keys($events);

            $result = $this->eventRepository->massDeleteByIds($allIds);

            $this->helper->getLogger()->info('Old events have been deleted', ['deleted' => $result]);
        } catch (\Exception $e) {
            $this->helper->getLogger()->error(
                'Technical error while cleaning old events',
                ['message' => $e->getMessage()]
            );
        }

        return $result;
    }
}
