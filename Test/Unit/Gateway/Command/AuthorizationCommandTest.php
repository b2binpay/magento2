<?php

namespace B2Binpay\Payment\Test\Unit\Gateway\Command;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use B2Binpay\Payment\Gateway\Config\Config;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;
use B2Binpay\Provider as B2Binpay;
use B2Binpay\Exception\B2BinpayException;
use B2Binpay\Payment\Gateway\Command\AuthorizationCommand;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AuthorizationCommandTest extends TestCase
{
    /**
     * @var Config | MockObject
     */
    private $config;

    /**
     * @var UrlInterface | MockObject
     */
    private $urlBuilder;

    /**
     * @var B2BinpayAdapterFactory | MockObject
     */
    private $adapterFactory;

    /**
     * @var LoggerInterface | MockObject
     */
    private $logger;

    /**
     * @var AuthorizationCommand
     */
    private $authorizationCommand;

    /**
     * @var PaymentDataObject | MockObject
     */
    private $paymentDO;

    /**
     * @var Payment | MockObject
     */
    private $payment;

    /**
     * @var Order | MockObject
     */
    private $order;

    /**
     * @var B2Binpay | MockObject
     */
    private $b2binpay;

    /**
     * @var array
     */
    private $commandSubject;

    public function setUp()
    {
        $this->config = $this->createMock(Config::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->adapterFactory = $this->createMock(B2BinpayAdapterFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->authorizationCommand = new AuthorizationCommand(
            $this->config,
            $this->urlBuilder,
            $this->adapterFactory,
            $this->logger
        );

        $storeId = 1;

        $this->paymentDO = $this->createMock(PaymentDataObject::class);
        $this->payment = $this->createMock(Payment::class);
        $this->order = $this->createMock(Order::class);
        $this->b2binpay = $this->createMock(B2Binpay::class);

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->payment->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getStoreId')
            ->willReturn($storeId);

        $this->order->method('getBaseCurrencyCode')
            ->willReturn('USD');

        $this->adapterFactory->method('create')
            ->willReturn($this->b2binpay);

        $this->config->method('getValue')
            ->will($this->onConsecutiveCalls(10, 1200));

        $this->commandSubject = [
            'amount' => '12.35',
            'payment' => $this->paymentDO
        ];
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     */
    public function testExecuteEmptyWallet()
    {
        $this->authorizationCommand->execute($this->commandSubject);
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     */
    public function testExecuteWrongWallet()
    {
        $this->payment->method('getAdditionalInformation')
            ->with(
                $this->equalTo('wallet')
            )
            ->willReturn($this->getWalletId());

        $this->b2binpay
            ->method('getWallet')
            ->will($this->throwException(new B2BinpayException));

        $this->authorizationCommand->execute($this->commandSubject);
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     */
    public function testExecuteBillError()
    {
        $this->payment->method('getAdditionalInformation')
            ->with(
                $this->equalTo('wallet')
            )
            ->willReturn($this->getWalletId());

        $this->b2binpay->method('getWallet')
            ->willReturn($this->getWallet());

        $this->b2binpay
            ->method('createBill')
            ->will($this->throwException(new B2BinpayException));

        $this->authorizationCommand->execute($this->commandSubject);
    }

    public function testExecute()
    {
        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(
                $this->equalTo('wallet')
            )
            ->willReturn($this->getWalletId());

        $this->b2binpay->method('getWallet')
            ->willReturn($this->getWallet());

        $this->b2binpay
            ->method('createBill')
            ->willReturn($this->getBill());

        $this->payment
            ->expects(static::once())
            ->method('setAdditionalInformation')
            ->with(
                $this->equalTo('redirect_url'),
                $this->equalTo($this->getBillUrl())
            )
            ->willReturn($this->payment);

        $this->payment
            ->expects(static::once())
            ->method('setTransactionId')
            ->with(
                $this->equalTo($this->getBillId())
            )
            ->willReturn($this->payment);

        $this->payment
            ->expects(static::once())
            ->method('setIsTransactionClosed')
            ->willReturn($this->payment);

        $this->payment
            ->expects(static::once())
            ->method('setIsTransactionPending')
            ->willReturn($this->payment);

        $this->order
            ->expects(static::once())
            ->method('addStatusToHistory')
            ->willReturn($this->order);

        $this->authorizationCommand->execute($this->commandSubject);
    }

    /**
     * @return string
     */
    private function getWalletId()
    {
        return '13';
    }

    /**
     * @return mixed
     */
    private function getWallet()
    {
        return json_decode(
            '{"id":13,"currency":{"alpha":"1000"}}'
        );
    }

    /**
     * @return int
     */
    private function getBillId()
    {
        return 99;
    }

    /**
     * @return string
     */
    private function getBillUrl()
    {
        return 'https://url';
    }

    /**
     * @return mixed
     */
    private function getBill()
    {
        return json_decode(
            '{"id":' . $this->getBillId() . ',"url":"' . $this->getBillUrl() . '"}'
        );
    }
}
