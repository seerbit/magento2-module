<?php

namespace Seerbit\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class ObserverBeforeSalesOrderPlace implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order **/
        $order = $observer->getEvent()->getOrder();

        if ($order) {
            $order->setCanSendNewEmailFlag(false)
                    ->setCustomerNoteNotify(false);
        }
    }
}
