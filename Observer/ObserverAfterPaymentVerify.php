<?php

namespace Seerbit\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

class ObserverAfterPaymentVerify implements ObserverInterface
{
    private OrderSender $orderSender;
    private LoggerInterface $logger;

    public function __construct(OrderSender $orderSender, LoggerInterface $logger)
    {
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        if ($order && $order->getStatus() === 'pending') {
            $order->setState(Order::STATE_PROCESSING)
                ->addStatusToHistory(Order::STATE_PROCESSING, __('SeerBit Payment Verified and Order is being processed'), true)
                ->setCanSendNewEmailFlag(true)
                ->setCustomerNoteNotify(true);
            try {
                $order->save();
                $this->orderSender->send($order, true);
            } catch (\Exception $e) {
                $this->logger->error('SeerBit: Failed to update order after payment verification', [
                    'order_id' => $order->getIncrementId(),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
