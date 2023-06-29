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


namespace CrowdSec\Engine\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\Store;

class CreateCmsBanBlock implements DataPatchInterface, PatchRevertableInterface
{
    /** @var string ban cms block identifier */
    public const CMS_BLOCK_BAN = 'crowdsec-engine-ban-wall';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->blockFactory->create()
            ->setTitle('CrowdSec Ban wall')
            ->setIdentifier(self::CMS_BLOCK_BAN)
            ->setIsActive(true)
            ->setContent('<div>IP banned by CrowdSec Engine</div>')
            ->setStores([Store::DEFAULT_STORE_ID])
            ->save();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $sampleCmsBlock = $this->blockFactory
            ->create()
            ->load(self::CMS_BLOCK_BAN, 'identifier');

        if ($sampleCmsBlock->getId()) {
            $sampleCmsBlock->delete();
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
