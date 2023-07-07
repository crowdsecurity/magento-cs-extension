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

namespace CrowdSec\Engine\Controller\Adminhtml\System\Config;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\CapiEngine\Watcher;

class Enroll extends Action implements HttpPostActionInterface
{
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
     * @param Watcher $watcher
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Helper $helper,
        Watcher $watcher
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
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

            $enrollKey = (string)$this->getRequest()->getParam('enroll_key');
            $engineName = (string)$this->getRequest()->getParam('engine_name');
            $forceEnroll = (bool)$this->getRequest()->getParam('force_enroll', false);

            $response = $this->watcher->enroll($engineName, $forceEnroll, $enrollKey);
            $capiResponse = $response['message'] ?? '';

            $message = __('Enroll request successfully sent. Response message was: %1', $capiResponse);
            $result = 1;
        } catch (Exception $e) {

            $result = false;
            $message = __('Technical error while enrolling: ' . $e->getMessage());
            $this->helper->getLogger()->error(
                'Technical error while pushing signals.',
                ['message' => $e->getMessage()]
            );
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'enrolled' => $result,
            'message' => $message,
        ]);
    }
}
