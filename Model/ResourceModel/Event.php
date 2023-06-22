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
namespace CrowdSec\Engine\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use CrowdSec\Engine\Helper\Data as Helper;


class Event extends AbstractDb
{

    /**
     * @var Helper
     */
    private $_helper;



    public function __construct(
        Context $context,
        Helper $helper,
        $connectionName = null

    ) {
        $this->_helper = $helper;
        parent::__construct($context, $connectionName);
    }

     /**
     * Delete all entries for some ids
     *
     * @param array $ids
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massDeleteForIds(array $ids):int
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $condition = $connection->quoteInto($this->getIdFieldName() . ' IN (?)', $ids);
        $deletedRows = $connection->delete($mainTable, $condition);

        return $deletedRows;
    }

    /**
     * Update all entries for some ids
     *
     * @param array $bind
     * @param array $ids
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massUpdateByIds(array $bind, array $ids): int
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $condition = $connection->quoteInto($this->getIdFieldName() . ' IN (?)', $ids);
        $updateRows = $connection->update($mainTable, $bind, $condition);

        return $updateRows;
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt($this->_helper->getCurrentGMTDate());

        return parent::_beforeSave($object);
    }

    protected function _construct()
    {
        $this->_init('crowdsec_event', 'event_id');
    }



}
