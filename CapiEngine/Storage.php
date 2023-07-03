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
use CrowdSec\Engine\Helper\Data as Helper;
use Magento\Framework\Encryption\EncryptorInterface;

class Storage implements StorageInterface
{

    private const CROWDSEC = 'crowdsec_engine';
    private const LAST_PUSH = 'last_push';
    private const MACHINE_ID = 'machine_id';
    private const PASSWORD = 'password';
    private const SCENARIOS = 'scenarios';
    private const TOKEN = 'token';
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var string
     */
    private $env;
    /**
     * @var FlagManager
     */
    private $flagManager;
    /**
     * @var Helper
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param FlagManager $flagManager
     * @param EncryptorInterface $encryptor
     * @param Helper $helper
     */
    public function __construct(
        FlagManager $flagManager,
        EncryptorInterface $encryptor,
        Helper $helper
    ) {
        $this->flagManager = $flagManager;
        $this->encryptor = $encryptor;
        $this->helper = $helper;
        $this->env = $this->helper->getEnv();
    }

    /**
     * Retrieve stored timestamp of the last signals push
     *
     * @return ?int
     */
    public function retrieveLastPush(): ?int
    {
        return $this->getConfigFlagValue(self::LAST_PUSH);
    }

    /**
     * @inheritdoc
     */
    public function retrieveMachineId(): ?string
    {
        return (string)$this->getConfigFlagValue(self::MACHINE_ID);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function retrievePassword(): ?string
    {
        $rawConfig = $this->getConfigFlagValue(self::PASSWORD);
        return $rawConfig ? $this->encryptor->decrypt($this->getConfigFlagValue(self::PASSWORD)) : null;
    }

    /**
     * @inheritdoc
     */
    public function retrieveScenarios(): ?array
    {
        return (array)$this->getConfigFlagValue(self::SCENARIOS);
    }

    /**
     * @inheritdoc
     */
    public function retrieveToken(): ?string
    {
        return (string)$this->getConfigFlagValue(self::TOKEN);
    }

    /**
     * Store a last signal push timestamp. Return true on success.
     *
     * @param int $lastPush
     * @return bool
     */
    public function storeLastPush(int $lastPush): bool
    {
        return $this->setConfigFlagValue(self::LAST_PUSH, $lastPush);
    }

    /**
     * @inheritdoc
     */
    public function storeMachineId(string $machineId): bool
    {
        return $this->setConfigFlagValue(self::MACHINE_ID, $machineId);
    }

    /**
     * @inheritdoc
     */
    public function storePassword(string $password): bool
    {
        return $this->setConfigFlagValue(self::PASSWORD, $this->encryptor->encrypt($password));
    }

    /**
     * @inheritdoc
     */
    public function storeScenarios(array $scenarios): bool
    {
        return $this->setConfigFlagValue(self::SCENARIOS, $scenarios);
    }

    /**
     * @inheritdoc
     */
    public function storeToken(string $token): bool
    {
        return $this->setConfigFlagValue(self::TOKEN, $token);
    }

    /**
     * Get Configuration Value From Flag By Code
     *
     * @param string $flagCode
     *
     * @return mixed
     */
    private function getConfigFlagValue(string $flagCode)
    {
        return $this->flagManager->getFlagData(self::CROWDSEC . '_' . $this->env . '_' . $flagCode);
    }

    /**
     * Set Configuration Value.
     *
     * @param string $flagCode
     * @param mixed $value
     *
     * @return void
     */
    private function setConfigFlagValue(string $flagCode, $value): bool
    {
        return $this->flagManager->saveFlag(self::CROWDSEC . '_' . $this->env . '_' . $flagCode, $value);
    }
}
