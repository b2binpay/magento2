<?php

namespace B2Binpay\Payment\Model\Adapter;

use Magento\Framework\ObjectManagerInterface;
use B2Binpay\Payment\Gateway\Config\Config;
use B2Binpay\Provider as B2BinpayAdapter;

class B2BinpayAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Creates instance of B2Binpay Provider.
     *
     * @param int|null $storeId if null is provided as an argument, then current scope will be resolved
     * by \Magento\Framework\App\Config\ScopeCodeResolver (useful for most cases) but for adminhtml area the store
     * should be provided as the argument for correct config settings loading.
     * @param string|null $authKey
     * @param string|null $authSecret
     * @param bool|null $testing
     * @return B2BinpayAdapter
     */
    public function create(int $storeId = null, string $authKey = null, string $authSecret = null, bool $testing = null)
    {
        return $this->objectManager->create(
            B2BinpayAdapter::class,
            [
                'authKey' => $authKey ?? $this->config->getValue('auth_key', $storeId),
                'authSecret' => $authSecret ?? $this->config->getValue('auth_secret', $storeId),
                'testing' => $testing ?? ('1' === $this->config->getValue('debug', $storeId))
            ]
        );
    }
}
