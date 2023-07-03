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
    public const MAX_ERROR_COUNT = 3;
    public const MAX_SIGNALS_PUSHED = 250;
    public const PUSH_TIME_DELAY = 10;
    public const EVENT_ID = 'event_id';
    public const IP = 'ip';
    public const LAST_EVENT_DATE = 'last_event_date';
    public const SCENARIO = 'scenario';
    public const STATUS_ALERT_TRIGGERED = 10;
    public const STATUS_CREATED = 0;
    public const STATUS_ID = 'status_id';
    public const STATUS_SIGNAL_PUSHED = 100;
    public const UPDATED_AT = 'updated_at';



    public function getContext();
    /**
     * @return int
     */
    public function getCount(): int;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return int
     */
    public function getErrorCount(): int;

    /**
     * @return int
     */
    public function getEventId(): int;

    /**
     * @return string
     */
    public function getIp(): string;

    /**
     * @return string
     */
    public function getLastEventDate(): string;

    /**
     * @return string
     */
    public function getScenario(): string;

    /**
     * @return int
     */
    public function getStatusId(): int;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;


    /**
     * @return EventInterface
     */
    public function setContext($context): EventInterface;

    /**
     * @param int $count
     * @return EventInterface
     */
    public function setCount(int $count): EventInterface;

    /**
     * @param string $createdAt
     * @return EventInterface
     */
    public function setCreatedAt(string $createdAt): EventInterface;

    /**
     * @param int $count
     * @return EventInterface
     */
    public function setErrorCount(int $count): EventInterface;

    /**
     * @param int $eventId
     * @return EventInterface
     */
    public function setEventId(int $eventId): EventInterface;

    /**
     * @param string $ip
     * @return EventInterface
     */
    public function setIp(string $ip): EventInterface;

    /**
     * @param string $date
     * @return EventInterface
     */
    public function setLastEventDate(string $date): EventInterface;

    /**
     * @param string $scenario
     * @return EventInterface
     */
    public function setScenario(string $scenario): EventInterface;

    /**
     * @param int $statusId
     * @return EventInterface
     */
    public function setStatusId(int $statusId): EventInterface;

    /**
     * @param string $updatedAt
     * @return EventInterface
     */
    public function setUpdatedAt(string $updatedAt): EventInterface;
}
