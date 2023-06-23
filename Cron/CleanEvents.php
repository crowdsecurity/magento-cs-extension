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
use CrowdSec\Engine\CapiEngine\Watcher;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Helper\Event as EventHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

class CleanEvents
{
    /**
     * @var EventHelper
     */
    private $eventHelper;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * Constructor
     *
     * @param Watcher $watcher
     * @param EventRepositoryInterface $eventRepository
     * @param Helper $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Watcher $watcher,
        EventHelper $eventHelper,
        Helper $helper,
        EventRepositoryInterface $eventRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->watcher = $watcher;
        $this->helper = $helper;
        $this->eventHelper = $eventHelper;
        $this->eventRepository = $eventRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Clean old signals
     *
     * @return void
     * @throws LocalizedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute(): int
    {
        //@TODO try catch log

        $lifetime = $this->helper->getEventLifetime();

        $threshold = date('Y-m-d h:i:s',strtotime("-$lifetime day"));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(EventInterface::LAST_EVENT_DATE, $threshold, 'lteq')
            ->create();

        $events = $this->eventRepository->getList($searchCriteria)->getItems();

        $allIds = array_keys($events);

        return $this->eventRepository->massDeleteByIds($allIds);



    }
}
