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

namespace CrowdSec\Engine\Model\Config\Source;

use CrowdSec\Engine\Constants;
use CrowdSec\RemediationEngine\CapiRemediation;
use Magento\Framework\Data\OptionSourceInterface;

class Fallback implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        $orderedRemediations = array_merge(CapiRemediation::ORDERED_REMEDIATIONS, [Constants::REMEDIATION_BYPASS]);
        foreach ($orderedRemediations as $remediation) {
            $result[] = ['value' => $remediation, 'label' => __("$remediation")];
        }

        return $result;
    }
}
