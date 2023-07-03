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

namespace CrowdSec\Engine\Api;

use CrowdSec\Engine\Api\Data\EventInterface;
use CrowdSec\Engine\Api\Data\EventSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface EventRepositoryInterface
{
    /**
     * Delete event.
     *
     * @param EventInterface $event
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(EventInterface $event): bool;
    /**
     * Delete event by ID.
     *
     * @param int $eventId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $eventId): bool;
    /**
     * Retrieve event.
     *
     * @param int $eventId
     * @return EventInterface
     * @throws LocalizedException
     */
    public function getById(int $eventId): EventInterface;
    /**
     * Retrieve events matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EventSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete events in mass
     *
     * @param array $ids
     * @return int
     */
    public function massDeleteByIds(array $ids): int;

    /**
     * Update events in mass
     *
     * @param array $bind
     * @param array $ids
     * @return int
     */
    public function massUpdateByIds(array $bind, array $ids): int;

    /**
     * Save event.
     *
     * @param EventInterface $event
     * @return EventInterface
     * @throws LocalizedException
     */
    public function save(EventInterface $event): EventInterface;
}
