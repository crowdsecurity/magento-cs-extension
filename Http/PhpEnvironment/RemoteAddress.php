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

namespace CrowdSec\Engine\Http\PhpEnvironment;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress as MagentoRemoteAddress;

/**
 * Extends to add alternative headers only for this module
 */
class RemoteAddress extends MagentoRemoteAddress
{

    public function __construct(
        RequestInterface $httpRequest,
        array $alternativeHeaders = [],
        array $trustedProxies = null
    ) {
        parent::__construct($httpRequest, $alternativeHeaders, $trustedProxies);
    }

}
