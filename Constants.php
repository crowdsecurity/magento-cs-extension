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

namespace CrowdSec\Engine;

use CrowdSec\CapiClient\Constants as CapiConstants;

class Constants extends CapiConstants
{

    /** @var string The last version of this module */
    public const VERSION = 'v0.0.1';

    /** @var string The user agent suffix used to send request to CAPI */
    public const USER_AGENT_SUFFIX = 'magento2';

    /** @var int Default duration for signal */
    public const DURATION = 3600;

}
