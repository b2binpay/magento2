<?php

namespace B2Binpay\Payment\Gateway\Command;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Command\CommandException;
use B2Binpay\Payment\Gateway\Config\Config;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;
use B2Binpay\Exception\B2BinpayException;
use Psr\Log\LoggerInterface;

class AuthorizationCommand implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var B2BinpayAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param UrlInterface $urlBuilder
     * @param B2BinpayAdapterFactory $adapterFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder,
        B2BinpayAdapterFactory $adapterFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->adapterFactory = $adapterFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $storeAmount = $commandSubject['amount'];
        $paymentDO = $commandSubject['payment'];

        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $storeId = $order->getStoreId();
        $walletId = $payment->getAdditionalInformation('wallet');

        if (empty($walletId)) {
            $this->logger->critical('Payment Error: empty Wallet id.');

            throw new CommandException(
                __('No currency provided.')
            );
        }

        $b2binpay = $this->adapterFactory->create($storeId);

        try {
            $wallet = $b2binpay->getWallet($walletId);
        } catch (B2BinpayException $e) {
            $this->logger->critical('Payment Error: ' . $e);
            throw new CommandException(__('Payment method error.'));
        }

        $amount = $b2binpay->convertCurrency(
            $storeAmount,
            $order->getBaseCurrencyCode(),
            $wallet->currency->alpha
        );

        $markup = $this->config->getValue('markup', $storeId);

        if (!empty($markup)) {
            $amount = $b2binpay->addMarkup(
                $amount,
                $wallet->currency->alpha,
                $markup
            );
        }

        try {
            $bill = $b2binpay->createBill(
                $wallet->id,
                $amount,
                $wallet->currency->alpha,
                $this->config->getValue('lifetime', $storeId),
                $order->getIncrementId(),
                $this->urlBuilder->getUrl($this->config::CALLBACK_URI)
            );
        } catch (B2BinpayException $e) {
            $this->logger->critical('Payment Error: ' . $e);
            throw new CommandException(__('Payment method error.'));
        }

        $payment->setAdditionalInformation('redirect_url', $bill->url)
            ->setTransactionId($bill->id)
            ->setIsTransactionClosed(false)
            ->setIsTransactionPending(true);

        $order->addStatusToHistory(
            $order->getStatus(),
            __('B2BinPay created new invoice for ') . $amount . $wallet->currency->alpha
        )->save();
    }
}
