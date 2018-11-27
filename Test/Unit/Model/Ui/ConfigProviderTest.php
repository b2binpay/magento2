<?php

namespace B2Binpay\Payment\Test\Unit\Model\Ui;

use PHPUnit\Framework\TestCase;
use B2Binpay\Payment\Gateway\Config\Config;
use B2Binpay\Payment\Model\Ui\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function testGetConfig()
    {
        $config = $this->createMock(Config::class);
        $configProvider = new ConfigProvider($config);

        $config->method('getValue')
            ->willReturn('{"id":1}');

        static::assertEquals(
            [
                'payment' => [
                    ConfigProvider::CODE => [
                        'currencyCodes' => [
                            'id' => 1
                        ]
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
