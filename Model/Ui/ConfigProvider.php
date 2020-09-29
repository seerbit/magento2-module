<?php
namespace Seerbit\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\Store as Store;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Checkout\Model\Cart;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    protected $method;
    protected $store;
    protected $order;
    protected $_storeManager;
    protected $_checkoutSession;
    protected $_guestCartManagement;
    private $cart;

    public function __construct(
        PaymentHelper $paymentHelper,
        Store $store,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        Session $checkoutSession,
        GuestCartManagementInterface $guestCartManagement,
        \Magento\Customer\Model\Session $customerSession,
        Cart $cart
    ) {
        $this->method = $paymentHelper->getMethodInstance(\Seerbit\Payment\Model\Payment\Standard::CODE);
        $this->store = $store;
        $this->order = $order;
        $this->cart = $cart;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $customerSession;
        $this->_guestCartManagement = $guestCartManagement;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $publicKey = $this->method->getConfigData('live_public_key');
        if ($this->method->getConfigData('test_mode')) {
            $publicKey = $this->method->getConfigData('test_public_key');
        }

        return [
            'payment' => [
                \Seerbit\Payment\Model\Payment\Standard::CODE => [
                    'public_key' => $publicKey,
                    'store' => $this->_storeManager->getStore()->getName(),
                    'customer_name' => $this->getGuestCustomerName(),
                    'api_url' => $this->store->getBaseUrl() . 'rest/',
                    'restore_cart_url' => $this->store->getBaseUrl() . 'seerbit_payment/payment/restorecart',
                ]
            ]
        ];
    }

    public function getStore()
    {
        return $this->cart->getItems();
    }

    public function getPublicKey()
    {
        $publicKey = $this->method->getConfigData('live_public_key');
        if ($this->method->getConfigData('test_mode')) {
            $publicKey = $this->method->getConfigData('test_public_key');
        }
        return $publicKey;
    }

    public function getGuestCustomerName()
    {
        return $this->order->getBillingAddress();
    }
}
