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


namespace CrowdSec\Engine\Controller\Adminhtml\System\Config\Cache;

use CrowdSec\Engine\CapiEngine\Remediation;
use CrowdSec\Engine\Controller\Adminhtml\System\Config\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use CrowdSec\Engine\Helper\Data as Helper;
use Magento\Framework\Controller\Result\JsonFactory;

class Clear extends Action implements HttpPostActionInterface
{
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var Remediation
     */
    private $remediation;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Remediation $remediation
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Remediation $remediation,
        Helper $helper
    ) {
        parent::__construct($context);
        $this->remediation = $remediation;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Clear cache
     *
     * @return Json
     */
    public function execute(): Json
    {
        try {
            $cacheSystem = $this->helper->getCacheTechnology();
            $cacheOptions = $this->helper->getCacheSystemOptions();
            $cacheLabel = $cacheOptions[$cacheSystem] ?? __('Unknown');
            $message = __('CrowdSec cache (%1) has been cleared.', $cacheLabel);
            $result = $this->remediation->getCacheStorage()->clear();
        } catch (\Exception $e) {
            $this->helper->getLogger()->error('Error while clearing cache', [
                'type' => 'M2_EXCEPTION_WHILE_CLEARING_CACHE',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $result = false;
            $message = __('Technical error while clearing the cache: ' . $e->getMessage());
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'cleared' => $result,
            'message' => $message,
        ]);
    }
}
