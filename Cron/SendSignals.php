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

class SendSignals
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
     * @var Watcher
     */
    private $watcher;

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
        Helper $helper
    ) {
        $this->watcher = $watcher;
        $this->helper = $helper;
        $this->eventHelper = $eventHelper;
    }

    /**
     * Send signals to CAPI
     *
     * @return void
     * @throws LocalizedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute(): void
    {
        //@TODO try catch log

        $this->eventHelper->sendSignals($this->watcher, EventInterface::MAX_SIGNALS_SENT, EventInterface::MAX_ERROR_COUNT);

        //@TODO : cron pour clean old signals
    }
}
