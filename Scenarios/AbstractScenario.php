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

namespace CrowdSec\Engine\Scenarios;

use CrowdSec\Engine\Api\Data\EventInterface;

abstract class AbstractScenario
{

    protected $blackHole = 3600;

    protected $leakSpeed = 10;

    protected $bucketCapacity = 10;

    protected $duration = 3600;

    protected $description = '';

    protected $name = '';

    public function getBlackHole(): int
    {
        return (int) $this->blackHole;
    }

    public function getLeakSpeed(): int
    {
        return (int) $this->leakSpeed;
    }

    public function getBucketCapacity(): int
    {
        return (int) $this->bucketCapacity;
    }

    public function getDuration(): int
    {
        return (int) $this->duration;
    }

    public function getDescription(): string
    {
        return (string) $this->description;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function getLeakingBucketCount(EventInterface $event): int
    {
        $bucketFill = $event->getCount();
        $lastEventTime = (int)strtotime($event->getLastEventDate());
        $currentTime = time();
        $leakSpeed = $this->getLeakSpeed();

        $bucketFill++;
        $bucketFill -= floor(($currentTime - $lastEventTime) / $leakSpeed);
        if ($bucketFill <= 0) {
            return 0;
        }

        return (int)$bucketFill;
    }


    public function isBlackHoleFor(EventInterface $event):bool
    {

        return (int)strtotime($event->getLastEventDate()) + $this->getBlackHole() > time();

    }
}
