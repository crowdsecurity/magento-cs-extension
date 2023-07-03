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
use CrowdSec\Engine\Api\Data\EventSearchResultsInterfaceFactory;
use CrowdSec\Engine\Api\EventRepositoryInterface;
use CrowdSec\Engine\Model\ResourceModel\Event as ResourceEvent;
use CrowdSec\Engine\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EventCollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var EventFactory
     */
    private $eventFactory;
    /**
     * @var ResourceEvent
     */
    private $resource;
    /**
     * @var EventSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param ResourceEvent $resource
     * @param EventFactory $eventFactory
     * @param EventCollectionFactory $collectionFactory
     * @param EventSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceEvent                      $resource,
        EventFactory                       $eventFactory,
        EventCollectionFactory             $collectionFactory,
        EventSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface      $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->eventFactory = $eventFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function delete(EventInterface $event): bool
    {
        try {
            $this->resource->delete($event);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the event: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $eventId): bool
    {
        return $this->delete($this->getById($eventId));
    }

    /**
     * @inheritdoc
     */
    public function getById(int $eventId): EventInterface
    {
        $event = $this->eventFactory->create();
        $this->resource->load($event, $eventId);
        if (!$event->getId()) {
            throw new NoSuchEntityException(__('The event with the "%1" ID does not exist.', $eventId));
        }
        return $event;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function massDeleteByIds(array $ids): int
    {

        return $this->resource->massDeleteForIds($ids);
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function massUpdateByIds(array $bind, array $ids): int
    {
        return $this->resource->massUpdateByIds($bind, $ids);
    }

    /**
     * @inheritdoc
     */
    public function save(EventInterface $event): EventInterface
    {
        try {
            $this->resource->save($event);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the event: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Could not save the event: %1', __('Something went wrong while saving the event.')),
                $exception
            );
        }
        return $event;
    }
}
