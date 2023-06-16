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

namespace CrowdSec\Engine\Observer\User;


use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LoginFailed implements ObserverInterface
{

    /**
     * @var EventInterface
     */
    private $_event;
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

    public function __construct(
        EventRepositoryInterface       $eventRepository,
        EventInterface $event,
        Helper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_event = $event;
        $this->_eventRepository = $eventRepository;
        $this->_helper = $helper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(Observer $observer): LoginFailed
    {

        if(!$this->_helper->isScenarioEnabled(Helper::ADMIN_AUTH_FAILED_CODE)){
            return $this;
        }


        // @TODO mutualiser avec autre observer
        $ip = $this->_helper->getRemoteIp();
        $scenario = Helper::SCENARIO_ADMIN_AUTH_FAILED;
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(EventInterface::SCENARIO, $scenario)
            ->addFilter(EventInterface::IP, $ip)
            ->addFilter(
                EventInterface::STATUS_ID,
                [EventInterface::STATUS_SIGNAL_SENT],
                'nin'
            )
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $events = $this->_eventRepository->getList($searchCriteria);
        $firstItem = current($events->getItems());

        $this->_event = $firstItem ?: $this->_event;

        // @TODO est ce qu'on créer un nouvel event si déjà trigger et pas encore envoyé ?
        if($this->_event->getStatusId() !== EventInterface::STATUS_ALERT_TRIGGERED){
            //@TODO : update count depending on settings and other logic
            $count = $this->_event->getCount() + 1;

            $this->_event->setIp($ip)
                ->setScenario($scenario)
                ->setCount($count)
                ->setLastEventDate($this->_helper->getCurrentGMTDate())
            ;
            $this->_eventRepository->save($this->_event);
        }




        return $this;
    }
}
