<?php

namespace B2Binpay\Payment\Test\Unit\Controller;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Psr\Log\LoggerInterface;
use B2Binpay\Payment\Controller\Redirect\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testExecute()
    {
        $context = $this->createMock(Context::class);
        $checkoutSession = $this->createMock(CheckoutSession::class);
        $resultJsonFactory = $this->createMock(JsonFactory::class);
        $logger = $this->createMock(LoggerInterface::class);

        $redirect = new Index(
            $context,
            $checkoutSession,
            $resultJsonFactory,
            $logger
        );

        $orderModel = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSession->expects(static::once())
            ->method('getLastRealOrder')
            ->willReturn($orderModel);

        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderModel->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentModel);

        $paymentModel->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn($this->getValue());

        $jsonModel = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonFactory->expects(static::once())
            ->method('create')
            ->willReturn($jsonModel);

        $jsonModel->expects(static::once())
            ->method('setData')
            ->with($this->getData());

        $redirect->execute();
    }

    /**
     * @return string
     */
    private function getValue()
    {
        return 'test_url';
    }

    /**
     * @return array
     */
    private function getData()
    {
        return [
            'url' => $this->getValue()
        ];
    }
}
