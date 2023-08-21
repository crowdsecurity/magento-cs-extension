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

    /** @var string The "MEMCACHED" cache system */
    public const CACHE_SYSTEM_MEMCACHED = 'memcached';
    /** @var string The "PHPFS" cache system */
    public const CACHE_SYSTEM_PHPFS = 'phpfs';
    /** @var string The "REDIS" cache system */
    public const CACHE_SYSTEM_REDIS = 'redis';
    /**
     * @see https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/crons/custom-cron-reference.html
     */
    public const CRON_DISABLE = '0 0 30 2 *';
    /** @var string */
    public const CROWDSEC_ENGINE_CACHE_PATH = BP . '/var/cache/crowdsec';
    /** @var int Default duration for signal */
    public const DURATION = 14400;
    /** @var string The user agent suffix used to send request to CAPI */
    public const USER_AGENT_SUFFIX = 'magento2';
    /** @var string The last version of this module */
    public const VERSION = 'v1.0.0';
}
