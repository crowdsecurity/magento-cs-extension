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

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use CrowdSec\Engine\Helper\Data as Helper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Button extends Field
{

    /** @var Helper */
    protected $helper;

    /** @var string  */
    protected $template = 'CrowdSec_Engine::system/config/signals/send.phtml';

    /** @var string  */
    protected $oldTemplate = 'CrowdSec_Engine::system/config/signals/old/send.phtml';


    /**
     *
     * @param Helper $helper
     * @param Context $context
     * @param array $data
     *
     */
    public function __construct(
        Helper $helper,
        Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->helper= $helper;
    }

    /**
     * Set template to itself
     *
     * @return \CrowdSec\Engine\Block\Adminhtml\System\Config\Button
     */
    protected function _prepareLayout(): Button
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            // Prior to 2.4.0, there was no SecureHtmlRenderer class
            $this->setTemplate(class_exists(SecureHtmlRenderer::class) ?
                $this->template :
                $this->oldTemplate);
        }
        return $this;
    }


    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }
}
