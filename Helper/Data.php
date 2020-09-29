<?php
namespace Seerbit\Payment\Helper;

use Magento\Sales\Api\OrderManagementInterface;

class Data extends \Magento\Payment\Helper\Data
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     *
     * @var type
     */
    protected $_checkoutSession;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Utils constructor.
     * @param \Magento\Checkout\Model\Session $session
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $session
    ) {
        parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);
        $this->_storeManager = $storeManager;
        $this->session = $session;
    }

    /**
     * Get customer/shopper's name
     * @param $order
     * @return string
     */
    public function getOrderCustomerName($order)
    {
        $customerName = '';
        if ($order->getCustomerId() === null) {
            $customerName = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
        } else {
            $customerName =  $order->getCustomerName();
        }
        return trim($customerName);
    }

    /**
     * Restores quote
     *
     * @return bool
     */
    public function restoreQuote()
    {
        return $this->session->restoreQuote();
    }

    /**
     * @param $order
     * @return bool
     */
    public function processOrder($order)
    {
        if ($order->getState() != $order::STATE_PROCESSING) {
            $order->setStatus($order::STATE_PROCESSING);
            $order->setState($order::STATE_PROCESSING);
            //$order->setExtOrderId($orderNumber);
            $order->save();
            $customerNotified = $this->sendOrderEmail($order);
            $order->addStatusToHistory($order::STATE_PROCESSING, 'SeerBit :: Order has been paid.', $customerNotified);
            $order->save();
            return true;
        }
        return false;
    }
}
