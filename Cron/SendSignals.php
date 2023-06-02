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
use CrowdSec\Engine\Client\Watcher;


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
     * @param Helper $helper
     */
    public function __construct(
        Watcher $watcher,
        EventRepositoryInterface       $eventRepository,
        Helper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
        $this->_watcher = $watcher;
        $this->_eventRepository = $eventRepository;
        $this->_helper = $helper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     *  Send signals by batch
     *
     * @return void
     */
    public function execute(): void
    {

        //@TODO : if last push too recent, return early

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(EventInterface::STATUS_ID, EventInterface::STATUS_ALERT_TRIGGERED)
            ->addFilter(EventInterface::ERROR_COUNT, $this->_errorThreshold, 'lteq')
            ->create();

        $events = $this->_eventRepository->getList($searchCriteria)->getItems();
        $watcher = $this->_watcher->init();

        $signals = [];
        $sentEvents = [];
        $i = 0;
        /**
         * @var $event \CrowdSec\Engine\Model\Event
         */
        while ($event = array_shift($events)) {
            $i++;
            if ($i > $this->_max) {
                break;
            }
            $sentEvents[$event->getId()] = true;

            $lastEventTime = (int)strtotime($event->getLastEventDate());

            $signalDate = (new \DateTime())->setTimestamp($lastEventTime);
            try {
                // @TODO : retrieve duration from scenario settings
                $signals[] = $watcher->buildSimpleSignalForIp(
                    $event->getIp(),
                    $event->getScenario(),
                    $signalDate,
                    '',
                    3600
                );

            }
            catch (ClientException $e) {

                unset($sentEvents[$event->getId()]);
                $errorCount = $event->getErrorCount() + 1;
                $event->setErrorCount($errorCount);
                $this->_eventRepository->save($event);

                //@TODO log
            }
        }

        if ($signals) {
            $result = $watcher->pushSignals($signals);
            /*$this->logger->info('Pushed @count signals upstream.', [
                '@count' => $i,
            ]);*/
            //@TODO : en cas d'erreur on met increment tous les error_count de chaque signal et disabled eventuellement
            // @TODO: virer le statut disabled => on doit ajouter un inder à error_count et se base sur cette valeur
            // uniquement

            $sentIds = array_keys($sentEvents);
            $this->_eventRepository->massUpdateByIds(['status_id' => EventInterface::STATUS_SIGNAL_SENT], $sentIds);
        }


    }
}
