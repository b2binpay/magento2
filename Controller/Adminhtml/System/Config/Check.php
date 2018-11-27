<?php

namespace B2Binpay\Payment\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;
use B2Binpay\Exception\B2BinpayException;

/**
 * Class Check
 */
class Check extends Action
{
    const SECRET_MASK = '******';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var B2BinpayAdapterFactory
     */
    protected $adapterFactory;

    /**
     * Auth constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param B2BinpayAdapterFactory $adapterFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        B2BinpayAdapterFactory $adapterFactory
    ) {
        $this->request = $context->getRequest();
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->adapterFactory = $adapterFactory;
        parent::__construct($context);
    }

    /**
     * Admin controller for auth params test
     *
     * - Check API Key/Secret
     * - Check Wallet ids
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $storeId = $this->storeManager->getStore()->getId();
        $authKey = $this->request->getParam('auth_key');
        $authSecret = $this->request->getParam('auth_secret');
        $wallets = $this->request->getParam('wallets');
        $testing = ('1' === $this->request->getParam('is_test'));

        if (empty($authKey)) {
            return $result->setData([
                'success' => false,
                'message' => __('You need to fill Auth API Key')
            ]);
        }

        if (empty($authSecret)) {
            return $result->setData([
                'success' => false,
                'message' => __('You need to fill Auth API Secret')
            ]);
        }

        if (empty($wallets)) {
            return $result->setData([
                'success' => false,
                'message' => __('You need to fill Wallets')
            ]);
        }

        if ($authSecret === $this::SECRET_MASK) {
            $authSecret = null;
        }

        $b2binpay = $this->adapterFactory->create(
            $storeId,
            $authKey,
            $authSecret,
            $testing
        );

        try {
            $b2binpay->getAuthToken();
        } catch (B2BinpayException $e) {
            return $result->setData([
                'success' => false,
                'message' => __('Wrong Auth API Key/Secret')
            ]);
        }

        foreach (explode('_', $wallets) as $wallet) {
            try {
                $b2binpay->getWallet($wallet);
            } catch (B2BinpayException $e) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Wrong Wallet ID: ' . $wallet)
                ]);
            }
        }

        return $result->setData([
            'success' => true,
            'message' => __('Success')
        ]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('B2Binpay_Payment::config');
    }
}
