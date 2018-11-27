<?php

namespace B2Binpay\Payment\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use B2Binpay\Payment\Observer\DataAssignObserver;
use PHPUnit\Framework\TestCase;

class DataAssignObserverTest extends TestCase
{
    public function testExectute()
    {
        $observerContainer = $this->getMockBuilder(Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodFacade = $this->createMock(MethodInterface::class);
        $paymentInfoModel = $this->createMock(InfoInterface::class);
        $dataObject = new DataObject(
            [
                'additional_data' => [
                    'test_info' => 'test'
                ]
            ]
        );

        $observerContainer->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(static::exactly(2))
            ->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::METHOD_CODE, $paymentMethodFacade],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );

        $paymentMethodFacade->expects(static::once())
            ->method('getInfoInstance')
            ->willReturn($paymentInfoModel);

        $paymentInfoModel->expects(static::once())
            ->method('setAdditionalInformation')
            ->with(
                'test_info',
                'test'
            );

        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }
}
