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
use CrowdSec\Engine\Model\Event;
use Magento\Framework\Api\SearchCriteriaBuilder;
use CrowdSec\CapiClient\ClientException;
use CrowdSec\Engine\CapiEngine\Watcher;
use CrowdSec\Engine\Constants;
use Magento\Framework\Exception\LocalizedException;

class SendSignals
{
    /**
     * @var int
     */
    private $max = 250;

    private $errorThreshold = 3;

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
        EventRepositoryInterface       $eventRepository,
        Helper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->watcher = $watcher;
        $this->eventRepository = $eventRepository;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        //@TODO : if last push too recent, return early

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(EventInterface::STATUS_ID, EventInterface::STATUS_ALERT_TRIGGERED)
            ->addFilter(EventInterface::ERROR_COUNT, $this->errorThreshold, 'lteq')
            ->create();

        $events = $this->eventRepository->getList($searchCriteria)->getItems();




        $signals = [];
        $pushedEvents = [];
        $i = 0;
        /**
         * @var $event Event
         */
        while ($event = array_shift($events)) {
            $i++;
            if ($i > $this->max) {
                break;
            }
            $pushedEvents[$event->getId()] = true;

            $lastEventTime = (int)strtotime($event->getLastEventDate());

            $signalDate = (new \DateTime())->setTimestamp($lastEventTime);
            try {

                $context = $event->getContext();
                $duration = $context['duration'] ?? Constants::DURATION;

                $signals[] = $this->watcher->buildSimpleSignalForIp(
                    $event->getIp(),
                    $event->getScenario(),
                    $signalDate,
                    '',
                    $duration
                );

            }
            catch (ClientException $e) {

                unset($pushedEvents[$event->getId()]);
                $errorCount = $event->getErrorCount() + 1;
                $event->setErrorCount($errorCount);
                $this->eventRepository->save($event);

                //@TODO log
            }
        }

        if ($signals) {
            $pushedIds = array_keys($pushedEvents);
            try {
                $this->watcher->pushSignals($signals);

                $this->eventRepository->massUpdateByIds(['status_id' => EventInterface::STATUS_SIGNAL_SENT], $pushedIds);

                // @TODO log

            } catch (ClientException $e) {

                $this->eventRepository->massUpdateByIds(
                    ['error_count' => new \Zend_Db_Expr('error_count + 1')],
                    $pushedIds
                );

                //@TODO log
            }
        }
    }
}
