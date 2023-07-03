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
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class Event extends AbstractExtensibleModel implements EventInterface
{

    /**
     * {@inheritdoc}
     */
    protected $_eventObject = 'event';
    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'crowdsec_engine_event';
    private $serializer;

    public function __construct(
        Json $serializer,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->serializer = $serializer;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource,
            $resourceCollection, $data);
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getContext()
    {
        $context = $this->getData(self::CONTEXT);
        if($context === null){
            return null;
        }
        return $this->serializer->unserialize($context);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getCount(): int
    {
        return (int) $this->getData(self::COUNT);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getErrorCount(): int
    {
        return (int) $this->getData(self::ERROR_COUNT);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getEventId(): int
    {
        return (int) $this->getData(self::EVENT_ID);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getIp(): string
    {
        return (string) $this->getData(self::IP);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getLastEventDate(): string
    {
        return (string) $this->getData(self::LAST_EVENT_DATE);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getScenario(): string
    {
        return (string) $this->getData(self::SCENARIO);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getStatusId(): int
    {
        return (int) $this->getData(self::STATUS_ID);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $context
     * @return EventInterface
     * @throws \InvalidArgumentException
     */
    public function setContext($context): EventInterface
    {
        $context = $this->serializer->serialize($context);
        return $this->setData(self::CONTEXT, $context);
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
