<?php

namespace B2Binpay\Payment\Test\Unit\Controller\Callback;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;
use B2Binpay\Payment\Controller\Callback\Index;
use B2Binpay\Payment\Gateway\Validator\CallbackValidator;
use B2Binpay\AmountFactory;
use B2Binpay\Amount;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class IndexTest extends TestCase
{
    /**
     * @var Context | MockObject
     */
    private $context;

    /**
     * @var RawFactory | MockObject
     */
    private $rawResultFactory;

    /**
     * @var CallbackValidator | MockObject
     */
    private $validator;

    /**
     * @var ResultInterface | MockObject
     */
    private $validationResult;

    /**
     * @var LoggerInterface | MockObject
     */
    private $logger;

    /**
     * @var Index
     */
    private $callback;

    /**
     * @var Order | MockObject
     */
    private $order;

    /**
     * @var OrderFactory | MockObject
     */
    private $orderFactory;

    /**
     * @var Payment | MockObject
     */
    private $payment;

    /**
     * @var Http | MockObject
     */
    private $request;

    /**
     * @var Raw | MockObject
     */
    private $result;

    /**
     * @var AmountFactory | MockObject
     */
    private $amountFactory;

    /**
     * @var Amount | MockObject
     */
    private $amount;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->rawResultFactory = $this->createMock(RawFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->request = $this->createMock(Http::class);
        $this->result = $this->createMock(Raw::class);
        $this->validationResult = $this->createMock(ResultInterface::class);

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->createMock(CallbackValidator::class);
        $this->amountFactory = $this->createMock(AmountFactory::class);
        $this->amount = $this->createMock(Amount::class);

        $this->context->method('getRequest')
            ->willReturn($this->request);

        $this->rawResultFactory->method('create')
            ->willReturn($this->result);

        $this->order->method('getPayment')
            ->willReturn($this->payment);

        $this->validator->method('validate')
            ->with([
                'request' => $this->request
            ])
            ->willReturn($this->validationResult);

        $this->amountFactory->method('create')
            ->willReturn($this->amount);

        $this->amount->method('getValue')
            ->willReturn('1');

        $this->callback = new Index(
            $this->context,
            $this->rawResultFactory,
            $this->validator,
            $this->amountFactory,
            $this->logger
        );
    }

    public function testValidateFail()
    {
        $status = '500';
        $message = 'Error';

        $this->validationResult->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn([
                'status' => $status,
                'message' => $message
            ]);

        $this->result->expects(static::once())
            ->method('setStatusHeader')
            ->with(
                $this->equalTo($status),
                $this->equalTo('1.1'),
                $this->equalTo($message)
            );

        $this->callback->execute();
    }

    public function executeDataProvider()
    {
        return [
            [
                ['status' => '-2'],
                Order::STATE_CLOSED,
                __('B2BinPay payment error!')
            ],
            [
                ['status' => '-1'],
                Order::STATE_CANCELED,
                __('B2BinPay payment expired!')
            ],
            [
                [
                    'status' => '2',
                    'amount' => '1',
                    'actual_amount' => '1'
                ],
                Order::STATE_PROCESSING,
                __('B2BinPay payment complete!')
            ],
            [
                [
                    'status' => '2',
                    'amount' => '2',
                    'actual_amount' => '1',
                    'currency' => [
                        'iso' => '1002',
                        'alpha' => 'ETH'
                    ],
                    'pow' => 8
                ],
                Order::STATE_PAYMENT_REVIEW,
                __('B2BinPay received payment: 1ETH')
            ],
            [
                ['status' => '3'],
                Order::STATE_PAYMENT_REVIEW,
                __('B2BinPay payment freeze!')
            ],
            [
                ['status' => '4'],
                Order::STATE_CLOSED,
                __('B2BinPay payment closed!')
            ],
        ];
    }

    /**
     * @param $params
     * @param $orderStatus
     * @param $orderMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute($params, $orderStatus, $orderMessage)
    {
        $this->request->expects(static::once())
            ->method('getParams')
            ->willReturn($params);

        $this->validationResult->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn([
                'order' => $this->order
            ]);

        $this->validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->order->expects(static::once())
            ->method('addStatusToHistory')
            ->with(
                $this->equalTo($orderStatus),
                $this->equalTo($orderMessage)
            );

        $this->result->expects(static::once())
            ->method('setStatusHeader')
            ->with(
                $this->equalTo('200'),
                $this->equalTo('1.1'),
                $this->equalTo('OK')
            );

        $this->result->expects(static::once())
            ->method('setContents')
            ->with(
                $this->equalTo('OK')
            );

        $this->callback->execute();
    }

    public function testExecutePending()
    {
        $this->request->expects(static::once())
            ->method('getParams')
            ->willReturn(['status' => '1']);

        $this->validationResult->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn([
                'order' => $this->order
            ]);

        $this->validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->result->expects(static::once())
            ->method('setStatusHeader')
            ->with(
                $this->equalTo('200'),
                $this->equalTo('1.1'),
                $this->equalTo('OK')
            );

        $this->result->expects(static::once())
            ->method('setContents')
            ->with(
                $this->equalTo('OK')
            );

        $this->callback->execute();
    }
}
