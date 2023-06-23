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

namespace CrowdSec\Engine\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Enroll extends Button
{

    /**
     * Enrollment key field Name
     *
     * @var string
     */
    private $enrollKeyField = 'crowdsec_engine_general_enrollment_key';

    /**
     * Engine name field Name
     *
     * @var string
     */
    private $engineNameField = 'crowdsec_engine_general_engine_name';

    /**
     * Engine name field Name
     *
     * @var string
     */
    private $forceEnrollField = 'crowdsec_engine_general_force_enroll';


    /** @var string  */
    protected $template = 'CrowdSec_Engine::system/config/enroll.phtml';

    /** @var string  */
    protected $oldTemplate = 'CrowdSec_Engine::system/config/old/enroll.phtml';


    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = $originalData['button_label'];
        $this->addData(
            [
                'button_label' => $buttonLabel,
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('crowdsec-engine/system_config/enroll'),
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Get Enroll key field Name
     *
     * @return string
     */
    public function getEnrollKeyField(): string
    {
        return $this->enrollKeyField;
    }

    /**
     * Get engine name field Name
     *
     * @return string
     */
    public function getEngineNameField(): string
    {
        return $this->engineNameField;
    }

    /**
     * Get force enroll field Name
     *
     * @return string
     */
    public function getForceEnrollField(): string
    {
        return $this->forceEnrollField;
    }
}
