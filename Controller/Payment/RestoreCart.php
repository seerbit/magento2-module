<?php

namespace Seerbit\Payment\Controller\Payment;

use Magento\Sales\Model\Order;

class RestoreCart extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $order->registerCancellation("Payment failed or cancelled")->save();
        }

        $this->_checkoutSession->restoreQuote();
        $this->_redirect('checkout', ['_fragment' => 'payment']);
    }
}
