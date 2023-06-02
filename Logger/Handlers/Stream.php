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

namespace CrowdSec\Engine\Logger\Handlers;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;

class Stream extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/crowdsec-engine.log';

    public function __construct(
        int $loggerType,
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null
    ) {
        $this->loggerType = $loggerType;
        parent::__construct($filesystem, $filePath, $fileName);
    }

}
