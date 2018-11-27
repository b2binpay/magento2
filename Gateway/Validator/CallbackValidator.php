<?php

namespace B2Binpay\Payment\Gateway\Validator;

use Magento\Sales\Model\OrderFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;

class CallbackValidator extends AbstractValidator
{
    const UNAUTHORIZED = 'Unauthorized';
    const BAD_REQUEST = 'Bad Request';
    const UNAUTHORIZED_STATUS = '401';
    const BAD_REQUEST_STATUS = '400';

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var B2BinpayAdapterFactory
     */
    protected $adapterFactory;

    /**
     * CallbackValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param OrderFactory $orderFactory
     * @param B2BinpayAdapterFactory $adapterFactory
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        OrderFactory $orderFactory,
        B2BinpayAdapterFactory $adapterFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->adapterFactory = $adapterFactory;
        parent::__construct($resultFactory);
    }

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $auth = $validationSubject['request']->getHeader('Authorization');

        if (empty($auth)) {
            return $this->createResult(
                false,
                [
                    'status' => self::UNAUTHORIZED_STATUS,
                    'message' => self::UNAUTHORIZED
                ]
            );
        }

        $trackingId = $validationSubject['request']->getParam('tracking_id');

        if (empty($trackingId)) {
            return $this->createResult(
                false,
                [
                    'status' => self::BAD_REQUEST_STATUS,
                    'message' => self::BAD_REQUEST
                ]
            );
        }

        $order = $this->orderFactory->create()->loadByIncrementId($trackingId);

        if (empty($order->getEntityId())) {
            return $this->createResult(
                false,
                [
                    'status' => self::BAD_REQUEST_STATUS,
                    'message' => self::BAD_REQUEST
                ]
            );
        }

        $b2binpay = $this->adapterFactory->create($order->getStoreId());

        $checkAuth = $b2binpay->getAuthorization();

        if ($auth !== $checkAuth) {
            return $this->createResult(
                false,
                [
                    'status' => self::UNAUTHORIZED_STATUS,
                    'message' => self::UNAUTHORIZED
                ]
            );
        }

        return $this->createResult(
            true,
            [
                'order' => $order
            ]
        );
    }
}
