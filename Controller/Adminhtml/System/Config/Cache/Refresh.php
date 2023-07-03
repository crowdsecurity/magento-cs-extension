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

use CrowdSec\Bouncer\Controller\Adminhtml\System\Config\Action;
use CrowdSec\Engine\CapiEngine\Remediation;
use CrowdSec\Engine\Helper\Data as Helper;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class Refresh extends Action implements HttpPostActionInterface
{
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var Remediation
     */
    private $remediation;

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
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->remediation = $remediation;
    }

    /**
     * Refresh cache
     *
     * @return Json
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function execute(): Json
    {
        try {
            $refresh = $this->remediation->refreshDecisions();
            $new = $refresh['new']??0;
            $deleted = $refresh['deleted']??0;
            $cacheSystem = $this->helper->getCacheTechnology();
            $cacheOptions = $this->helper->getCacheSystemOptions();
            $cacheLabel = $cacheOptions[$cacheSystem] ?? __('Unknown');
            $message = __(
                'CrowdSec cache (%1) has been refreshed. New decision(s): %2. Deleted decision(s): %3',
                $cacheLabel,
                $new,
                $deleted
            );
            $result = 1;
        } catch (Exception $e) {
            $this->helper->getLogger()->error('Error while refreshing cache', [
                'type' => 'M2_EXCEPTION_WHILE_REFRESHING_CACHE',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $result = false;
            $message = __('Technical error while refreshing the cache: ' . $e->getMessage());
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'refresh' => $result,
            'message' => $message,
        ]);
    }
}
