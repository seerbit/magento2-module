<?php

namespace Seerbit\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Data extends AbstractHelper
{
    private CheckoutSession $checkoutSession;
    private OrderSender $orderSender;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderSender = $orderSender;
    }

    public function getOrderCustomerName(Order $order): string
    {
        if ($order->getCustomerId() === null) {
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress) {
                return trim($billingAddress->getFirstname() . ' ' . $billingAddress->getLastname());
            }
            return '';
        }
        return trim((string) $order->getCustomerName());
    }

    public function restoreQuote(): bool
    {
        return $this->checkoutSession->restoreQuote();
    }

    public function processOrder(Order $order): bool
    {
        if ($order->getState() !== Order::STATE_PROCESSING) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus(Order::STATE_PROCESSING)
                ->addStatusToHistory(Order::STATE_PROCESSING, __('SeerBit :: Order has been paid.'), true);
            $order->save();
            $this->orderSender->send($order, true);
            return true;
        }
        return false;
    }
}
