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

namespace CrowdSec\Engine\Scenarios;

use Magento\Framework\App\Response\Http as HttpResponse;

class PagesScan extends AbstractScenario
{

    protected $description = 'Detect pages scan';

    protected $name = 'magento2/scan-4xx';

    /**
     * @var array
     */
    protected $detectedScans = [HttpResponse::STATUS_CODE_404, HttpResponse::STATUS_CODE_403];


    public function getDetectedScans(): array
    {
        return $this->detectedScans;
    }
}
