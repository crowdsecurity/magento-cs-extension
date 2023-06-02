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

namespace CrowdSec\Engine\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use CrowdSec\Engine\Logger\Logger;
use CrowdSec\Engine\Logger\Handlers\StreamFactory;
use CrowdSec\Engine\Logger\Handlers\DisabledFactory;

class Data extends Config
{

    /**
     * @var DateTime
     */
    private $_coreDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $_dateTime;

    /**
     * @var StreamFactory
     */
    private $_streamLoggerFactory;

    private $_disabledLoggerFactory;

    /**
     * @var Logger
     */
    private $_finalLogger;

    public function __construct(
        Context $context,
        DateTime $coreDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Logger $logger,
        StreamFactory $streamFactory,
        DisabledFactory $disabledFactory,
    ) {
        $this->_coreDate = $coreDate;
        $this->_dateTime = $dateTime;
        $this->_streamLoggerFactory = $streamFactory;
        $this->_disabledLoggerFactory = $disabledFactory;
        $this->_finalLogger = $logger;

        parent::__construct($context);
    }

    /**
     * Get the current IP, even if it's the IP of a proxy
     *
     * @return string
     */
    public function getRemoteIp(): string
    {
        return $this->_remoteAddress->getRemoteAddress();
    }

    /**
     * @return string
     */
    public function getCurrentGMTDate(): string
    {
        return $this->_dateTime->formatDate($this->_coreDate->gmtDate());
    }

    /**
     * Manage logger and handler
     *
     * @return Logger|null
     */
    public function getLogger(): ?Logger
    {
        $handler = $this->getLogLevel() ?
            $this->_streamLoggerFactory->create(['loggerType' => $this->getLogLevel()]) :
            $this->_disabledLoggerFactory->create();;

        $this->_finalLogger->pushHandler($handler);

        return $this->_finalLogger;
    }

}
