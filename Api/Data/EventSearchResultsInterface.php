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

namespace CrowdSec\Engine\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface EventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get event list.
     *
     * @return EventInterface[]
     */
    public function getItems(): array;
    /**
     * Set event list.
     *
     * @param EventInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
