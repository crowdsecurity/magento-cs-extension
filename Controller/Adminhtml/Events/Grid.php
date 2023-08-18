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
namespace CrowdSec\Engine\Controller\Adminhtml\Events;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Grid extends BackendAction implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'CrowdSec_Engine::events_list';

    private const MENU_ID = 'CrowdSec_Engine::events_list';
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend((__('CrowdSec Engine')));

        return $resultPage;
    }
}
