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

use CrowdSec\Common\Exception;
use CrowdSec\Engine\Constants;
use CrowdSec\Engine\Http\PhpEnvironment\RemoteAddress;
use CrowdSec\Engine\Logger\Handlers\DisabledFactory;
use CrowdSec\Engine\Logger\Handlers\StreamFactory;
use CrowdSec\Engine\Logger\Logger;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

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
     * @var DisabledFactory
     */
    private $_disabledLoggerFactory;
    /**
     * @var Logger
     */
    private $_finalLogger;
    /**
     * @var Logger
     */
    private $_selfLogger;
    /**
     * @var StreamFactory
     */
    private $_streamLoggerFactory;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param DateTime $coreDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param Logger $logger
     * @param StreamFactory $streamFactory
     * @param DisabledFactory $disabledFactory
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        DateTime $coreDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Logger $logger,
        StreamFactory $streamFactory,
        DisabledFactory $disabledFactory,
        RemoteAddress $remoteAddress
    ) {
        $this->_coreDate = $coreDate;
        $this->_dateTime = $dateTime;
        $this->_streamLoggerFactory = $streamFactory;
        $this->_disabledLoggerFactory = $disabledFactory;
        $this->_selfLogger = $logger;
        $this->remoteAddress = $remoteAddress;

        parent::__construct($context);
    }

    /**
     * Get cache system options
     *
     * @return array
     */
    public function getCacheSystemOptions(): array
    {
        return [
            Constants::CACHE_SYSTEM_PHPFS => __('File system'),
            Constants::CACHE_SYSTEM_REDIS => __('Redis'),
            Constants::CACHE_SYSTEM_MEMCACHED => __('Memcached')
        ];
    }

    /**
     * Get current GMT date.
     *
     * @return string
     */
    public function getCurrentGMTDate(): string
    {
        return $this->_dateTime->formatDate($this->_coreDate->gmtDate());
    }

    /**
     * Manage logger and handler
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        if ($this->_finalLogger === null) {
            $this->_finalLogger = $this->_selfLogger;
            $handler = $this->getLogLevel() ?
                $this->_streamLoggerFactory->create(['loggerType' => $this->getLogLevel()]) :
                $this->_disabledLoggerFactory->create();

            $this->_finalLogger->pushHandler($handler);
        }

        return $this->_finalLogger;
    }

    /**
     * Get the current real IP (event if there is a proxy behind)
     *
     * @return string
     */
    public function getRealIp(): string
    {
        // Alternative headers have been set in DI
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Check if a cron expression is valid.
     *
     * @param string $expr
     * @return void
     * @throws Exception
     */
    public function validateCronExpr(string $expr)
    {
        $e = preg_split('#\s+#', $expr, -1, PREG_SPLIT_NO_EMPTY);
        if (count($e) < 5 || count($e) > 6) {
            throw new Exception("Invalid cron expression: $expr");
        }
    }
}
