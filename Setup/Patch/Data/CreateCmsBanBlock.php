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

        $content =
            <<<EOF
<style>
    * {
        margin: 0;
        padding: 0;
    }

    html {
        height: 100%;
        color: black;
    }

    body {
        background: #eee;
        font-family: Arial, Helvetica, sans-serif;
        height: 100%;
    }

    .container {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .main {
        background: white;
        padding: 50px 50px 30px 50px;
        box-shadow: #00000033 0px 3px 3px -2px, #00000024 0px 3px 4px 0px, #0000001f 0px 1px 8px 0px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 1;
        position: relative;
    }

    h1 {
        padding: 10px;
    }

    .desc {
        font-size: 1.2em;
        margin-bottom: 30px;
    }

    .powered {
        margin-top: 30px;
        font-size: small;
        color: #AAA;
    }

    .warning {
        width: 40px;
        display: inline-block;
        vertical-align: -4px;
    }

    .logo {
        width: 17px;
        display: inline-block;
        vertical-align: -12px;
        margin-left: 5px;
    }

    a {
        color: #AAA;
    }

</style>
<div class="container">
    <div class="main">
        <h1>Your IP {{var ip}} has been blocked</h1>
        <p>Please look <a href="https://app.crowdsec.net/cti/{{var ip}}" target="_blank">CrowdSec CTI</a></p>
        <p class="powered">This security check has been powered by
                  <a href="https://crowdsec.net/" target="_blank" rel="noopener">CrowdSec</a>
        </p>
    </div>
</div>
EOF;
        $this->blockFactory->create()
            ->setTitle('CrowdSec Ban wall')
            ->setIdentifier(self::CMS_BLOCK_BAN)
            ->setIsActive(true)
            ->setContent($content)
            ->setStores([Store::DEFAULT_STORE_ID])
            ->save();

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
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
