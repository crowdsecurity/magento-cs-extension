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
use CrowdSec\RemediationEngine\Constants as RemediationConstants;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;

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

    private const ORIGIN_CAPI = 'CAPI';// Constants::ORIGIN_CAPI is lowercase but CrowdSec uses uppercase

    /**
     * @param Remediation $remediation
     * @param Helper $helper
     * @param BackendUrlInterface $backendUrl
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Remediation $remediation,
        Helper $helper,
        BackendUrlInterface $backendUrl,
        Context $context,
        array $data = []
    ) {
        $this->remediation = $remediation;
        $this->helper = $helper;
        $this->backendUrl = $backendUrl;
        $data = array_merge($data, [
            'origin_capi' => self::ORIGIN_CAPI,
            'origin_lists' => Constants::ORIGIN_LISTS,
            'origin_crowdsec' => Constants::ORIGIN
        ]);
        parent::__construct($context, $data);
    }

    /**
     * Retrieves origins count
     *
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getOriginsCount(): array
    {
        $result = [
            self::ORIGIN_CAPI => 0,
            Constants::ORIGIN_LISTS => 0,
            Constants::ORIGIN => 0
        ];
        $originsCount = $this->remediation->getOriginsCount();
        foreach ($originsCount as $origin => $remediations) {
            foreach ($remediations as $count) {
                if ($origin === Constants::ORIGIN) {
                    $result[Constants::ORIGIN] += $count;
                }
                if ($origin === self::ORIGIN_CAPI) {
                    $result[self::ORIGIN_CAPI] += $count;
                }
                if (strpos($origin, Constants::ORIGIN_LISTS . RemediationConstants::ORIGIN_LISTS_SEPARATOR) === 0) {
                    $result[Constants::ORIGIN_LISTS] += $count;
                }
            }
        }

        return $result;
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
