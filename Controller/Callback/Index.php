<?php

namespace B2Binpay\Payment\Controller\Callback;

use Magento\Sales\Model\Order;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use B2Binpay\Payment\Gateway\Validator\CallbackValidator;
use B2Binpay\AmountFactory;
use Psr\Log\LoggerInterface;

class Index extends Action implements CsrfAwareActionInterface
{
    /**
     * @var RawFactory
     */
    protected $rawResultFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CallbackValidator
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AmountFactory
     */
    private $amountFactory;

    /**
     * Callback controller.
     *
     * @param Context $context
     * @param RawFactory $rawResultFactory
     * @param CallbackValidator $validator
     * @param AmountFactory $amountFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RawFactory $rawResultFactory,
        CallbackValidator $validator,
        AmountFactory $amountFactory,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->rawResultFactory = $rawResultFactory;
        $this->validator = $validator;
        $this->amountFactory = $amountFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $request = $this->context->getRequest();
        $result = $this->rawResultFactory->create();

        $validationResult = $this->validator->validate(['request' => $request]);

        $validation = $validationResult->getFailsDescription();

        if (!$validationResult->isValid()) {
            $result->setStatusHeader($validation['status'], '1.1', $validation['message']);

            return $result;
        }

        $order = $validation['order'];
        $payment = $order->getPayment();
        $params = $request->getParams();

        $billStatus = (string)$params['status'];

        if ('2' === $billStatus) {
            if ($params['amount'] === $params['actual_amount']) {
                $totalDue = $order->getTotalDue();

                $payment->authorize(false, $totalDue);
                $payment->registerCaptureNotification($totalDue);

                $order->addStatusToHistory(
                    $order::STATE_PROCESSING,
                    __('B2BinPay payment complete!')
                );
            } else {
                $actualAmount = $this->amountFactory->create(
                    $params['actual_amount'],
                    $params['currency']['iso'],
                    $params['pow']
                )->getValue();

                $order->addStatusToHistory(
                    $order::STATE_PAYMENT_REVIEW,
                    __('B2BinPay received payment: ' . $actualAmount . $params['currency']['alpha'])
                );
            }
        }

        $stateList = $this->getStateDesc();

        if (!empty($stateList[$billStatus])) {
            $order->addStatusToHistory(
                $stateList[$billStatus]['state'],
                $stateList[$billStatus]['message']
            );
        }

        $order->save();

        $result->setStatusHeader('200', '1.1', 'OK');
        $result->setContents('OK');

        return $result;
    }

    /**
     * @return array
     */
    private function getStateDesc()
    {
        return [
            '-2' => [
                'state' => Order::STATE_CLOSED,
                'message' => __('B2BinPay payment error!')
            ],
            '-1' => [
                'state' => Order::STATE_CANCELED,
                'message' => __('B2BinPay payment expired!')
            ],
            '3' => [
                'state' => Order::STATE_PAYMENT_REVIEW,
                'message' => __('B2BinPay payment freeze!')
            ],
            '4' => [
                'state' => Order::STATE_CLOSED,
                'message' => __('B2BinPay payment closed!')
            ]
        ];
    }
}
