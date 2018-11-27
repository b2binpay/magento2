<?php

namespace B2Binpay\Payment\Test\Unit\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;
use B2Binpay\Payment\Controller\Adminhtml\System\Config\Check;
use B2Binpay\Provider as B2Binpay;
use B2Binpay\Exception\B2BinpayException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CheckTest extends TestCase
{
    /**
     * @var Context | MockObject
     */
    private $context;

    /**
     * @var Http | MockObject
     */
    private $request;

    /**
     * @var Check
     */
    private $check;

    /**
     * @var JsonFactory | MockObject
     */
    private $resultJsonFactory;

    /**
     * @var StoreManagerInterface | MockObject
     */
    private $storeManager;

    /**
     * @var B2BinpayAdapterFactory | MockObject
     */
    private $adapterFactory;

    /**
     * @var B2Binpay | MockObject
     */
    private $b2binpay;

    /**
     * @var Json | MockObject
     */
    private $result;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactory = $this->createMock(JsonFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->adapterFactory = $this->createMock(B2BinpayAdapterFactory::class);
        $this->b2binpay = $this->createMock(B2Binpay::class);
        $this->request = $this->createMock(Http::class);
        $this->result = $this->createMock(Json::class);
        $store = $this->createMock(Store::class);

        $this->context->method('getRequest')
            ->willReturn($this->request);

        $this->check = new Check(
            $this->context,
            $this->resultJsonFactory,
            $this->storeManager,
            $this->adapterFactory
        );

        $this->resultJsonFactory->method('create')
            ->willReturn($this->result);

        $this->storeManager->method('getStore')
            ->willReturn($store);

        $store->method('getId')
            ->willReturn(1);
    }

    public function executeErrorsDataProvider()
    {
        return [
            'empty_key' => [
                'params' => [null, '2', null, '1'],
                'success' => false,
                'message' => 'You need to fill Auth API Key'
            ],
            'empty_secret' => [
                'params' => ['1', null, null, '1'],
                'success' => false,
                'message' => 'You need to fill Auth API Secret'
            ],
            'empty_wallets' => [
                'params' => ['1', '2', null, '1'],
                'success' => false,
                'message' => 'You need to fill Wallets'
            ]
        ];
    }

    /**
     * @dataProvider executeErrorsDataProvider
     * @param array $params
     * @param bool $success
     * @param string $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecuteErrors(array $params, bool $success, string $message)
    {
        list($authKey, $authSecret, $wallets, $testing) = $params;

        $this->request->method('getParam')
            ->will($this->onConsecutiveCalls($authKey, $authSecret, $wallets, $testing));

        $this->result->expects(static::once())
            ->method('setData')
            ->with($this->equalTo([
                'success' => $success,
                'message' => __($message)
            ]));

        $this->check->execute();
    }

    public function executeDataProvider()
    {
        return [
            'success' => [
                'params' => ['1', '1', '1_2', '1'],
                'success' => true,
                'message' => 'Success'
            ],
            'masked_secret_success' => [
                'params' => ['1', Check::SECRET_MASK, '1_2', '1'],
                'success' => true,
                'message' => 'Success'
            ]
        ];
    }

    /**
     * @dataProvider executeDataProvider
     * @param array $params
     * @param bool $success
     * @param string $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecute(array $params, bool $success, string $message)
    {
        list($authKey, $authSecret, $wallets, $testing) = $params;

        $this->request->method('getParam')
            ->will($this->onConsecutiveCalls($authKey, $authSecret, $wallets, $testing));

        if ($authSecret === Check::SECRET_MASK) {
            $authSecret = null;
        }

        $this->adapterFactory->method('create')
            ->with(
                $this->equalTo(1),
                $this->equalTo($authKey),
                $this->equalTo($authSecret),
                $this->equalTo($testing)
            )
            ->willReturn($this->b2binpay);

        $this->result->expects(static::once())
            ->method('setData')
            ->with($this->equalTo([
                'success' => $success,
                'message' => __($message)
            ]));

        $this->check->execute();
    }

    public function executeExceptionsDataProvider()
    {
        return [
            'getAuthToken_exception' => [
                'method' => 'getAuthToken',
                'params' => ['1', '1', '1_2', '1'],
                'success' => false,
                'message' => 'Wrong Auth API Key/Secret'
            ],
            'getWallet_exception' => [
                'method' => 'getWallet',
                'params' => ['1', '1', '1_2', '1'],
                'success' => false,
                'message' => 'Wrong Wallet ID: 1'
            ]
        ];
    }

    /**
     * @dataProvider executeExceptionsDataProvider
     * @param string $method
     * @param array $params
     * @param bool $success
     * @param string $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecuteExceptions(string $method, array $params, bool $success, string $message)
    {
        list($authKey, $authSecret, $wallets, $testing) = $params;

        $this->request->method('getParam')
            ->will($this->onConsecutiveCalls($authKey, $authSecret, $wallets, $testing));

        if ($authSecret === Check::SECRET_MASK) {
            $authSecret = null;
        }

        $this->adapterFactory->method('create')
            ->with(
                $this->equalTo(1),
                $this->equalTo($authKey),
                $this->equalTo($authSecret),
                $this->equalTo($testing)
            )
            ->willReturn($this->b2binpay);

        $this->b2binpay
            ->method($method)
            ->will($this->throwException(new B2BinpayException));

        $this->result->expects(static::once())
            ->method('setData')
            ->with($this->equalTo([
                'success' => $success,
                'message' => __($message)
            ]));

        $this->check->execute();
    }
}
