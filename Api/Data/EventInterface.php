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

interface EventInterface
{
    public const BLACK_HOLE_DEFAULT = 3600;
    public const CONTEXT = 'context';
    public const COUNT = 'count';
    public const CREATED_AT = 'created_at';
    public const ERROR_COUNT = 'error_count';
    public const EVENT_ID = 'event_id';
    public const IP = 'ip';
    public const LAST_EVENT_DATE = 'last_event_date';
    public const MAX_ERROR_COUNT = 3;
    public const MAX_SIGNALS_PUSHED = 250;
    public const PUSH_TIME_DELAY = 10;
    public const SCENARIO = 'scenario';
    public const STATUS_ALERT_TRIGGERED = 10;
    public const STATUS_CREATED = 0;
    public const STATUS_ID = 'status_id';
    public const STATUS_SIGNAL_PUSHED = 100;
    public const UPDATED_AT = 'updated_at';

    /**
     * Retrieve "context' data.
     *
     * @return mixed
     */
    public function getContext();
    /**
     * Retrieve "count" data.
     *
     * @return int
     */
    public function getCount(): int;

    /**
     * Retrieve "created_at" data.
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Retrieve "error_count" data.
     *
     * @return int
     */
    public function getErrorCount(): int;

    /**
     * Retrieve "event_id" data.
     *
     * @return int
     */
    public function getEventId(): int;

    /**
     * Retrieve "ip" data.
     *
     * @return string
     */
    public function getIp(): string;

    /**
     * Retrieve "last_event_date" data.
     *
     * @return string
     */
    public function getLastEventDate(): string;

    /**
     * Retrieve "scenario" data.
     *
     * @return string
     */
    public function getScenario(): string;

    /**
     * Retrieve "status_id" data.
     *
     * @return int
     */
    public function getStatusId(): int;

    /**
     * Retrieve "updated_at" data.
     *
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * Set "context" data.
     *
     * @param mixed $context
     * @return EventInterface
     */
    public function setContext($context): EventInterface;

    /**
     * Set "count" data.
     *
     * @param int $count
     * @return EventInterface
     */
    public function setCount(int $count): EventInterface;

    /**
     * Set "created_at" data.
     *
     * @param string $createdAt
     * @return EventInterface
     */
    public function setCreatedAt(string $createdAt): EventInterface;

    /**
     * Set "error_count" data.
     *
     * @param int $count
     * @return EventInterface
     */
    public function setErrorCount(int $count): EventInterface;

    /**
     * Set "event_id" data.
     *
     * @param int $eventId
     * @return EventInterface
     */
    public function setEventId(int $eventId): EventInterface;

    /**
     * Set "ip" data.
     *
     * @param string $ip
     * @return EventInterface
     */
    public function setIp(string $ip): EventInterface;

    /**
     * Set "last_event_date" data.
     *
     * @param string $date
     * @return EventInterface
     */
    public function setLastEventDate(string $date): EventInterface;

    /**
     * Set "scenario" data.
     *
     * @param string $scenario
     * @return EventInterface
     */
    public function setScenario(string $scenario): EventInterface;

    /**
     * Set "status_id" data.
     *
     * @param int $statusId
     * @return EventInterface
     */
    public function setStatusId(int $statusId): EventInterface;

    /**
     * Set "updated_at" data.
     *
     * @param string $updatedAt
     * @return EventInterface
     */
    public function setUpdatedAt(string $updatedAt): EventInterface;
}
