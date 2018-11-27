<?php

namespace B2Binpay\Payment\Controller\Redirect;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Redirect Controller.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $context
        );

        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        $payment = $order->getPayment();

        $post_data = [
            'url' => $payment->getAdditionalInformation('redirect_url')
        ];

        $result = $this->resultJsonFactory->create();

        return $result->setData($post_data);
    }
}
