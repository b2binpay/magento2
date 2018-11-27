<?php

namespace B2Binpay\Payment\Test\Unit\Model\Adapter;

use Magento\Framework\ObjectManager\ObjectManager;
use B2Binpay\Payment\Gateway\Config\Config;
use B2Binpay\Payment\Model\Adapter\B2BinpayAdapterFactory;
use B2Binpay\Provider as B2BinpayAdapter;
use PHPUnit\Framework\TestCase;

class B2BinpayAdapterFactoryTest extends TestCase
{
    public function testCreate()
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $config = $this->createMock(Config::class);

        $objectManager->method('create')
            ->willReturn(new B2BinpayAdapter('1', '2', true));

        $adapter = (new B2BinpayAdapterFactory($objectManager, $config))->create();
        $this->assertInstanceOf(B2BinpayAdapter::class, $adapter);
    }
}
