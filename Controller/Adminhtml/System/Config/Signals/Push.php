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
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Helper\Event as EventHelper;
use CrowdSec\Engine\CapiEngine\Watcher;

class Push extends Action implements HttpPostActionInterface
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
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var Watcher
     */
    private $watcher;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Helper $helper
     * @param EventHelper $eventHelper
     * @param Watcher $watcher
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
     */
    public function execute(): Json
    {
        try {

            $result = $this->eventHelper->pushSignals(
                $this->watcher,
                EventInterface::MAX_SIGNALS_PUSHED,
                EventInterface::MAX_ERROR_COUNT,
                EventInterface::PUSH_TIME_DELAY
            );

            $message = __(
                '%1 pushed signals (%2 errors for %3 candidates).',
                $result['pushed'] ?? 0,
                $result['errors'] ?? 0,
                $result['candidates'] ?? 0
            );
            $result = 1;
        } catch (Exception $e) {

            $result = false;
            $message = __('Technical error while pushing signals: ' . $e->getMessage());
            $this->helper->getLogger()->critical(
                'Technical error while pushing signals.',
                ['message' => $e->getMessage()]
            );
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'pushed' => $result,
            'message' => $message,
        ]);
    }
}
