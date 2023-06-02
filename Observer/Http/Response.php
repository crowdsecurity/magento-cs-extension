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

namespace CrowdSec\Engine\Observer\Http;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Helper\Data as Helper;
use Laminas\Http\Response as HttpResponse;


class Response implements ObserverInterface
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
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;
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
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\ActionFlag $actionFlag
    ) {
        $this->_event = $event;
        $this->_eventRepository = $eventRepository;
        $this->_helper = $helper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_actionFlag = $actionFlag;
    }

    public function execute(Observer $observer): Response
    {

        //@TODO: check if feature is enabled
        /**
         * @var $response \Magento\Framework\HTTP\PhpEnvironment\Response
         */
        $response = $observer->getEvent()->getResponse();

        if(in_array($response->getStatusCode(), [HttpResponse::STATUS_CODE_404, HttpResponse::STATUS_CODE_403])){

            $ip = $this->_helper->getRemoteIp();
            $scenario = EventInterface::SCENARIO_SCAN_4XX;
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



        }


        // getRemediation for IP and send 403 ?

    /*    $response->setBody('<h1>IP banned by CrowdSec</h1>')->setStatusCode
        (\Magento\Framework\App\Response\Http::STATUS_CODE_403);
        $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);*/



        return $this;
    }
}
