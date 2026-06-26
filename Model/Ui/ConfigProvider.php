<?php

namespace Seerbit\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Seerbit\Payment\Model\Payment\Standard;

class ConfigProvider implements ConfigProviderInterface
{
    private $method;
    private StoreManagerInterface $storeManager;

    public function __construct(
        PaymentHelper $paymentHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->method = $paymentHelper->getMethodInstance(Standard::CODE);
        $this->storeManager = $storeManager;
    }

    public function getConfig(): array
    {
        $publicKey = $this->method->getConfigData('live_public_key');
        if ($this->method->getConfigData('test_mode')) {
            $publicKey = $this->method->getConfigData('test_public_key');
        }
        $store = $this->storeManager->getStore();
        return [
            'payment' => [
                Standard::CODE => [
                    'public_key' => $publicKey,
                    'store' => $store->getName(),
                    'api_url' => $store->getBaseUrl() . 'rest/' . $store->getCode() . '/',
                    'restore_cart_url' => $store->getBaseUrl() . 'seerbit_payment/payment/restorecart',
                ]
            ]
        ];
    }
}
