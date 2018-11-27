<?php

namespace B2Binpay\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use B2Binpay\Payment\Gateway\Config\Config;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'b2binpay';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $wallets = json_decode($this->config->getValue('wallets'), true);

        return [
            'payment' => [
                self::CODE => [
                    'currencyCodes' => $wallets
                ]
            ]
        ];
    }
}
