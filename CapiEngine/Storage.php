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

namespace CrowdSec\Engine\CapiEngine;

use CrowdSec\CapiClient\Storage\StorageInterface;
use Magento\Framework\FlagManager;

class Storage implements StorageInterface
{

    private const CROWDSEC = 'crowdsec_engine';

    private const MACHINE_ID = 'machine_id';

    private const PASSWORD = 'password';

    private const TOKEN = 'token';

    private const SCENARIOS = 'scenarios';

    /**
     * @var FlagManager
     */
    private FlagManager $_flagManager;

    public function __construct(
        FlagManager $flagManager
    ) {
        $this->_flagManager = $flagManager;
    }

    /**
     *. Set Configuration Value
     *
     *. @param string $flagCode
     *. @param mixed $value
     *
     * @return void
     */
    private function setConfigFlagValue($flagCode, $value): bool
    {
        //@TODO : handle env config DEv and PROD

        return $this->_flagManager->saveFlag(self::CROWDSEC . '_DEV_' . $flagCode, $value);
    }

    /**
     * Get Configuration Value From Flag By Code
     *
     * @param string $flagCode
     *
     * @return mixed
     */
    private function getConfigFlagValue($flagCode): mixed
    {
        //@TODO : handle env config

        return $this->_flagManager->getFlagData(self::CROWDSEC . '_DEV_' . $flagCode);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveMachineId(): ?string
    {
        return (string)$this->getConfigFlagValue(self::MACHINE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function retrievePassword(): ?string
    {
        return (string)$this->getConfigFlagValue(self::PASSWORD);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveScenarios(): ?array
    {
        return (array)$this->getConfigFlagValue(self::SCENARIOS);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveToken(): ?string
    {
        return (string)$this->getConfigFlagValue(self::TOKEN);
    }

    /**
     * {@inheritdoc}
     */
    public function storeMachineId(string $machineId): bool
    {
        return $this->setConfigFlagValue(self::MACHINE_ID, $machineId);
    }

    /**
     * {@inheritdoc}
     */
    public function storePassword(string $password): bool
    {
        return $this->setConfigFlagValue(self::PASSWORD, $password);
    }

    /**
     * {@inheritdoc}
     */
    public function storeScenarios(array $scenarios): bool
    {
        return $this->setConfigFlagValue(self::SCENARIOS, $scenarios);
    }

    /**
     * {@inheritdoc}
     */
    public function storeToken(string $token): bool
    {
        return $this->setConfigFlagValue(self::TOKEN, $token);
    }

}
