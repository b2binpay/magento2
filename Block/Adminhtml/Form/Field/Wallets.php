<?php

namespace B2Binpay\Payment\Block\Adminhtml\Form\Field;

/**
 * Wallets Adminhtml frontend model
 */
class Wallets extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('wallet', ['label' => __('Wallet ID'), 'class' => 'required-entry b2binpay-wallet']);
        $this->addColumn('currency', ['label' => __('Currency [store]'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Wallet');
    }
}
