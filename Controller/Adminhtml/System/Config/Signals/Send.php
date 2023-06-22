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

namespace CrowdSec\Engine\Controller\Adminhtml\System\Config\Signals;

use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Controller\Adminhtml\System\Config\Action;
use Exception;
use LogicException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use CrowdSec\Engine\Helper\Data as Helper;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use CrowdSec\Engine\Helper\Event as EventHelper;
use CrowdSec\Engine\CapiEngine\Watcher;

class Send extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var EventHelper
     */
    private $eventHelper;
    /**
     * @var Watcher
     */
    private $watcher;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Helper $helper,
        EventHelper $eventHelper,
        Watcher $watcher
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->eventHelper = $eventHelper;
        $this->watcher = $watcher;
    }

    /**
     * Refresh cache
     *
     * @return Json
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws CacheException
     */
    public function execute(): Json
    {
        try {

            $result = $this->eventHelper->sendSignals($this->watcher, EventInterface::MAX_SIGNALS_SENT,
                EventInterface::MAX_ERROR_COUNT);

            $message = __('%1 signals sent (%2 errors for %3 candidates).', $result['sent'] ?? 0,
                $result['errors'] ?? 0,
                $result['candidates'] ?? 0
            );
            $result = 1;
        } catch (Exception $e) {

            $result = false;
            $message = __('Technical error while sending signals: ' . $e->getMessage());
            //@TODO log errors
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'send' => $result,
            'message' => $message,
        ]);
    }
}
