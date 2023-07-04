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
namespace CrowdSec\Engine\Cron;

use CrowdSec\Engine\CapiEngine\Remediation;
use CrowdSec\Engine\Helper\Data as Helper;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class PruneCache
{

    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var Remediation
     */
    private $remediation;

    /**
     * Constructor
     *
     * @param Remediation $remediation
     * @param Helper $helper
     */
    public function __construct(
        Remediation $remediation,
        Helper $helper
    ) {
        $this->remediation = $remediation;
        $this->helper = $helper;
    }

    /**
     * Cron task that prune File system cache.
     *
     * @return void
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function execute(): void
    {
        try {
            $result = $this->remediation->getCacheStorage()->prune();
            $this->helper->getLogger()->info('Cache has been pruned by cron', [$result]);
        } catch (\Exception $e) {
            $this->helper->getLogger()->error('Error while pruning cache', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
