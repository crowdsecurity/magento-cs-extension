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

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Event\ManagerInterface;
use CrowdSec\Engine\Scenarios\UserEnum;
use CrowdSec\Engine\Scenarios\PagesScan;

class SignalScenario implements OptionSourceInterface
{
    /**
     * @var ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var PagesScan
     */
    private PagesScan $pageScan;
    /**
     * @var UserEnum
     */
    private UserEnum $userEnum;

    public function __construct(
        ManagerInterface $eventManager,
        UserEnum $userEnum,
        PagesScan $pagesScan
    ) {
        $this->_eventManager = $eventManager;
        $this->userEnum = $userEnum;
        $this->pageScan = $pagesScan;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $list = new \ArrayObject([
            $this->pageScan,
            $this->userEnum
        ]);

        // Allow other modules to add more scenarios.
        $this->_eventManager->dispatch('crowdsec_engine_signal_scenario_list', ['list' => $list]);

        $result = [];
        $i = 0;
        $scenarios = $list->getArrayCopy();
        foreach ($scenarios as $scenario) {
            $result[$i]['value'] = $scenario->getName();
            $result[$i]['label'] = __($scenario->getDescription());
            $i++;
        }

        return $result;
    }
}
