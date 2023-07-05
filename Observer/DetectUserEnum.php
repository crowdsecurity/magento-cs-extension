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

namespace CrowdSec\Engine\Observer;

use CrowdSec\Engine\Helper\Data as Helper;
use CrowdSec\Engine\Scenarios\UserEnum;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\ResourceModel\User;

class DetectUserEnum implements ObserverInterface
{
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var UserEnum
     */
    private $scenario;
    /**
     * @var User
     */
    private $user;

    /**
     * Constructor.
     *
     * @param Helper $helper
     * @param UserEnum $scenario
     * @param User $user
     */
    public function __construct(
        Helper $helper,
        UserEnum $scenario,
        User $user
    ) {
        $this->helper = $helper;
        $this->scenario = $scenario;
        $this->user = $user;
    }

    /**
     * Handle user enumeration detection.
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): DetectUserEnum
    {
        try {
            $scenarioName = $this->scenario->getName();
            if (!$this->helper->isScenarioEnabled($scenarioName)) {
                return $this;
            }

            $userName = $observer->getEvent()->getUserName();

            $user = $this->user->loadByUsername($userName);
            // We only detect non-existent user enumeration
            if (!isset($user['user_id'])) {
                $this->scenario->process($userName);
            }
        } catch (\Exception $e) {
            $this->helper->getLogger()->error(
                'Technical error while detecting user enumeration',
                ['message' => $e->getMessage()]
            );
        }

        return $this;
    }
}
