<?php

namespace B2Binpay\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey('additional_data') !== null) {
            foreach ($data->getDataByKey('additional_data') as $key => $value) {
                $paymentInfo->setAdditionalInformation(
                    $key,
                    $value
                );
            }
        }
    }
}
