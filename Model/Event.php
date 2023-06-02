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

namespace CrowdSec\Engine\Model;

use CrowdSec\Engine\Api\Data\EventInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Event extends AbstractExtensibleModel implements EventInterface
{

    /**
     * {@inheritdoc}
     */
    public function getCount(): int
    {
        return (int) $this->getData(self::COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCount(): int
    {
        return (int) $this->getData(self::ERROR_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventId(): int
    {
        return (int) $this->getData(self::EVENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getIp(): string
    {
        return $this->getData(self::IP);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastEventDate(): string
    {
        return $this->getData(self::LAST_EVENT_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getScenario(): string
    {
        return $this->getData(self::SCENARIO);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusId(): int
    {
        return (int) $this->getData(self::STATUS_ID);
    }

     /**
     * {@inheritdoc}
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCount(int $count): EventInterface
    {
        return $this->setData(self::COUNT, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(string $createdAt): EventInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorCount(int $count): EventInterface
    {
        return $this->setData(self::ERROR_COUNT, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventId(int $eventId): EventInterface
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * {@inheritdoc}
     */
    public function setIp(string $ip): EventInterface
    {
        return $this->setData(self::IP, $ip);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastEventDate(string $date): EventInterface
    {
        return $this->setData(self::LAST_EVENT_DATE, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function setScenario(string $scenario): EventInterface
    {
        return $this->setData(self::SCENARIO, $scenario);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusId(int $statusId): EventInterface
    {
        return $this->setData(self::STATUS_ID, $statusId);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(string $updatedAt): EventInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Event::class);
        $this->setIdFieldName('event_id');
    }

}
