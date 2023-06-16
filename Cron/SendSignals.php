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
use CrowdSec\CapiClient\ClientException;
use CrowdSec\Engine\CapiEngine\Watcher;
use CrowdSec\Engine\Constants;


class SendSignals
{
    /**
     * @var int
     */
    private $_max = 250;

    private $_errorThreshold = 3;

    /**
     * @var EventRepositoryInterface
     */
    private $_eventRepository;
    /**
     * @var Helper
     */
    private $_helper;
    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    private $_watcher;

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
        $this->_watcher = $watcher;
        $this->_eventRepository = $eventRepository;
        $this->_helper = $helper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Send signals
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void
    {

        //@TODO : if last push too recent, return early

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(EventInterface::STATUS_ID, EventInterface::STATUS_ALERT_TRIGGERED)
            ->addFilter(EventInterface::ERROR_COUNT, $this->_errorThreshold, 'lteq')
            ->create();

        $events = $this->_eventRepository->getList($searchCriteria)->getItems();




        $signals = [];
        $pushedEvents = [];
        $i = 0;
        /**
         * @var $event \CrowdSec\Engine\Model\Event
         */
        while ($event = array_shift($events)) {
            $i++;
            if ($i > $this->_max) {
                break;
            }
            $pushedEvents[$event->getId()] = true;

            $lastEventTime = (int)strtotime($event->getLastEventDate());

            $signalDate = (new \DateTime())->setTimestamp($lastEventTime);
            try {
                $duration = Constants::DURATION;
                $mapping = $this->_helper->getScenariosMapping();
                if(isset($mapping[$event->getScenario()])){
                    $rule = $this->_helper->getScenarioRule($mapping[$event->getScenario()]);
                    $duration = !empty($rule['duration'])?$rule['duration']:$duration;
                }
                $signals[] = $this->_watcher->buildSimpleSignalForIp(
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
                $this->_eventRepository->save($event);

                //@TODO log
            }
        }

        if ($signals) {
            $pushedIds = array_keys($pushedEvents);
            try {
                $this->_watcher->pushSignals($signals);

                $this->_eventRepository->massUpdateByIds(['status_id' => EventInterface::STATUS_SIGNAL_SENT], $pushedIds);

                // @TODO log

            } catch (ClientException $e) {

                $this->_eventRepository->massUpdateByIds(
                    ['error_count' => new \Zend_Db_Expr('error_count + 1')],
                    $pushedIds
                );

                //@TODO log
            }
        }
    }
}
