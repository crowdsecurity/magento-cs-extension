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

namespace CrowdSec\Engine\Block\Adminhtml\Report;

use CrowdSec\Engine\CapiEngine\Remediation;
use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Constants;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Metrics extends Template
{
    /**
     * @var Remediation
     */
    private $remediation;
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var BackendUrlInterface
     */
    private $backendUrl;

    /**
     * @param Remediation $remediation
     * @param Helper $helper
     * @param BackendUrlInterface $backendUrl
     * @param Context $context
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Remediation $remediation,
        Helper $helper,
        BackendUrlInterface $backendUrl,
        Context $context,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->remediation = $remediation;
        $this->helper = $helper;
        $this->backendUrl = $backendUrl;
        $data = array_merge($data, [
            'origin_capi' => Constants::ORIGIN_CAPI,
            'origin_lists' => Constants::ORIGIN_LISTS,
            'origin_crowdsec' => Constants::ORIGIN
        ]);
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Retrieves origin count cached item
     *
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getOriginsCount(): array
    {

        return $this->remediation->getOriginsCount();
    }

    /**
     * Retrieves "should bounce ban" setting
     *
     * @return bool
     */
    public function isBanBouncingEnabled(): bool
    {
        return $this->helper->shouldBounceBan();
    }

    /**
     * Retrieves "should ban locally" setting
     *
     * @return bool
     */
    public function isLocalBanEnabled(): bool
    {
        return $this->helper->shouldBanLocally();
    }

    /**
     * Retrieves "fallback" setting
     *
     * @return string
     */
    public function getFallback(): string
    {
        return $this->helper->getFallbackRemediation();
    }

    /**
     * Retrieves settings url
     *
     * @return string
     */
    public function getSettingsUrl()
    {
        return $this->backendUrl->getUrl('adminhtml/system_config/edit/', ['section' => 'crowdsec_engine']);
    }
}
