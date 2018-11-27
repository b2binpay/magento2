<?php

namespace B2Binpay\Payment\Test\Unit\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use B2Binpay\Payment\Helper\Wallets as WalletsHelper;
use B2Binpay\Payment\Model\System\Config\Backend\Wallets;
use PHPUnit\Framework\TestCase;

class WalletsTest extends TestCase
{
    public function testBeforeSave()
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $config = $this->createMock(ScopeConfigInterface::class);
        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $walletsHelper = $this->createMock(WalletsHelper::class);

        $wallets = $this->getMockBuilder(Wallets::class)
            ->setConstructorArgs([
                $context,
                $registry,
                $config,
                $cacheTypeList,
                $walletsHelper
            ])
            ->setMethods([
                'getValue',
                'setValue'
            ])
            ->getMock();

        $value = 'val';
        $newValue = 'newVal';

        $wallets->expects(static::once())
            ->method('getValue')
            ->willReturn($value);

        $walletsHelper->expects(static::once())
            ->method('makeStorableArrayFieldValue')
            ->with($this->equalTo($value))
            ->willReturn($newValue);

        $wallets->expects(static::once())
            ->method('setValue')
            ->with($this->equalTo($newValue));

        $wallets->beforeSave();
    }
}
