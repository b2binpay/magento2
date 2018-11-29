<?php

namespace B2Binpay\Payment\Test\Unit\Gateway\Validator;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Framework\App\Request\Http;
use B2Binpay\Payment\Gateway\Validator\CallbackValidator;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;
use B2Binpay\Provider as B2Binpay;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CallbackValidatorTest extends TestCase
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Order
     */
    private $orderModel;

    /**
     * @var B2BinpayAdapterFactory
     */
    private $adapterFactory;

    /**
     * @var ResultInterface | MockObject
     */
    private $resultMock;

    /**
     * @var B2Binpay
     */
    private $b2binpay;

    /**
     * @var Magento\Payment\Gateway\Validator\ResultInterfaceFactory | MockObject
     */
    private $resultFactory;

    /**
     * @var Http | MockObject
     */
    private $request;

    /**
     * @var CallbackValidator
     */
    private $validator;

    public function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->createMock(ResultInterface::class);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->adapterFactory = $this->createMock(B2BinpayAdapterFactory::class);
        $this->b2binpay = $this->createMock(B2Binpay::class);
        $this->request = $this->createMock(Http::class);

        $this->orderModel = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new CallbackValidator(
            $this->resultFactory,
            $this->orderFactory,
            $this->adapterFactory
        );
    }

    public function testEmptyAuthorization()
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $this->createFailResult(
                    CallbackValidator::UNAUTHORIZED_STATUS,
                    CallbackValidator::UNAUTHORIZED
                )
            )
            ->willReturn($this->resultMock);

        static::assertInstanceOf(
            ResultInterface::class,
            $this->validator->validate(['request' => $this->request])
        );
    }

    public function testEmptyTrackingId()
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $this->createFailResult(
                    CallbackValidator::BAD_REQUEST_STATUS,
                    CallbackValidator::BAD_REQUEST
                )
            )
            ->willReturn($this->resultMock);

        $this->request->expects(static::once())
            ->method('getHeader')
            ->with($this->equalTo('Authorization'))
            ->willReturn($this->getAuthorisation());

        static::assertInstanceOf(
            ResultInterface::class,
            $this->validator->validate(['request' => $this->request])
        );
    }

    public function testOrderNotFound()
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $this->createFailResult(
                    CallbackValidator::BAD_REQUEST_STATUS,
                    CallbackValidator::BAD_REQUEST
                )
            )
            ->willReturn($this->resultMock);

        $this->request->expects(static::once())
            ->method('getHeader')
            ->with($this->equalTo('Authorization'))
            ->willReturn($this->getAuthorisation());

        $this->request->expects(static::once())
            ->method('getParam')
            ->with($this->equalTo('tracking_id'))
            ->willReturn($this->getTrackingId());

        $this->orderFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->orderModel);

        $this->orderModel->expects(static::once())
            ->method('loadByIncrementId')
            ->with($this->equalTo($this->getTrackingId()))
            ->willReturn($this->orderModel);

        static::assertInstanceOf(
            ResultInterface::class,
            $this->validator->validate(['request' => $this->request])
        );
    }

    public function testWrongAuthorisation()
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $this->createFailResult(
                    CallbackValidator::UNAUTHORIZED_STATUS,
                    CallbackValidator::UNAUTHORIZED
                )
            )
            ->willReturn($this->resultMock);

        $this->request->expects(static::once())
            ->method('getHeader')
            ->with($this->equalTo('Authorization'))
            ->willReturn($this->getAuthorisation());

        $this->request->expects(static::once())
            ->method('getParam')
            ->with($this->equalTo('tracking_id'))
            ->willReturn($this->getTrackingId());

        $this->orderFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->orderModel);

        $this->orderModel->expects(static::once())
            ->method('loadByIncrementId')
            ->with($this->equalTo($this->getTrackingId()))
            ->willReturn($this->orderModel);

        $this->orderModel->expects(static::once())
            ->method('getEntityId')
            ->willReturn($this->getOrderId());

        $this->orderModel->expects(static::once())
            ->method('getStoreId')
            ->willReturn($this->getStoreId());

        $this->adapterFactory->expects(static::once())
            ->method('create')
            ->with($this->equalTo($this->getStoreId()))
            ->willReturn($this->b2binpay);

        $this->b2binpay->expects(static::once())
            ->method('getAuthorization')
            ->willReturn('Basic: Wrong');

        static::assertInstanceOf(
            ResultInterface::class,
            $this->validator->validate(['request' => $this->request])
        );
    }

    public function testValidate()
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'isValid' => true,
                    'failsDescription' => [
                        'order' => $this->orderModel
                    ],
                    'errorCodes' => []
                ]
            )
            ->willReturn($this->resultMock);

        $this->request->expects(static::once())
            ->method('getHeader')
            ->with($this->equalTo('Authorization'))
            ->willReturn($this->getAuthorisation());

        $this->request->expects(static::once())
            ->method('getParam')
            ->with($this->equalTo('tracking_id'))
            ->willReturn($this->getTrackingId());

        $this->orderFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->orderModel);

        $this->orderModel->expects(static::once())
            ->method('loadByIncrementId')
            ->with($this->equalTo($this->getTrackingId()))
            ->willReturn($this->orderModel);

        $this->orderModel->expects(static::once())
            ->method('getEntityId')
            ->willReturn($this->getOrderId());

        $this->orderModel->expects(static::once())
            ->method('getStoreId')
            ->willReturn($this->getStoreId());

        $this->adapterFactory->expects(static::once())
            ->method('create')
            ->with($this->equalTo($this->getStoreId()))
            ->willReturn($this->b2binpay);

        $this->b2binpay->expects(static::once())
            ->method('getAuthorization')
            ->willReturn($this->getAuthorisation());

        static::assertInstanceOf(
            ResultInterface::class,
            $this->validator->validate(['request' => $this->request])
        );
    }

    /**
     * @param string $status
     * @param string $message
     * @return array
     */
    private function createFailResult(string $status, string $message)
    {
        return [
            'isValid' => false,
            'failsDescription' => [
                'status' => $status,
                'message' => $message
            ],
            'errorCodes' => []
        ];
    }

    /**
     * @return string
     */
    private function getAuthorisation()
    {
        return 'Basic: Auth';
    }

    /**
     * @return string
     */
    private function getTrackingId()
    {
        return '1234';
    }

    /**
     * @return int
     */
    private function getOrderId()
    {
        return 321;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return 123;
    }
}
