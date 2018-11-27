<?php

namespace B2Binpay\Payment\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Checker
 */
class Checker extends Field
{
    /**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'B2Binpay_Payment::system/config/check.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getTemplate()) {
            $this->setTemplate(static::CHECK_TEMPLATE);
        }

        return $this;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('b2binpay/system_config/check');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $buttonData = ['id' => 'b2binpay_check_button', 'label' => __('Check Auth')];
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($buttonData);

        return $button->toHtml();
    }
}
