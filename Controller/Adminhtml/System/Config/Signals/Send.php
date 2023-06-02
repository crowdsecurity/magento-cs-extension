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

class Send extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Helper $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
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
            //@TODO
            //@TODO : if last push too recent, return early
            $message = 'OK';
            $result = 1;
        } catch (Exception $e) {

            $result = false;
            $message = __('Technical error while sending signals: ' . $e->getMessage());
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'send' => $result,
            'message' => $message,
        ]);
    }
}
