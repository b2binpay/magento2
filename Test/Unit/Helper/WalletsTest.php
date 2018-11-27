<?php

namespace B2Binpay\Payment\Test\Unit\Helper;

use B2Binpay\Payment\Helper\Wallets;
use PHPUnit\Framework\TestCase;

class WalletsTest extends TestCase
{
    protected $walletsHelper;

    public function setUp()
    {
        $this->walletsHelper = new Wallets();
    }

    public function testMakeArrayFieldValue()
    {
        $value = $this->walletsHelper->makeArrayFieldValue($this->getStorableArrayFieldValue());
        $this->assertEquals($this->getArrayFieldValue(), $value);

        $value = $this->walletsHelper->makeArrayFieldValue('null');
        $this->assertEquals(null, $value);

        $value = $this->walletsHelper->makeArrayFieldValue('[]');
        $this->assertEquals([], $value);

        $value = $this->walletsHelper->makeArrayFieldValue(null);
        $this->assertEquals(null, $value);
    }

    public function testMakeStorableArrayFieldValue()
    {
        $value = $this->walletsHelper->makeStorableArrayFieldValue($this->getArrayFieldValue());
        $this->assertEquals($this->getStorableArrayFieldValue(), $value);

        $value = $this->walletsHelper->makeStorableArrayFieldValue(null);
        $this->assertEquals('null', $value);
    }

    /**
     * @return array
     */
    private function getArrayFieldValue()
    {
        return [
            [
                'wallet' => 1,
                'currency' => "BTC"
            ],
            [
                'wallet' => 2,
                'currency' => "ETH"
            ]
        ];
    }

    /**
     * @return string
     */
    private function getStorableArrayFieldValue()
    {
        return '{"1":"BTC","2":"ETH"}';
    }
}
