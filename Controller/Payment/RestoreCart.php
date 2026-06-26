<?php

namespace Seerbit\Payment\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class RestoreCart implements HttpGetActionInterface
{
    private CheckoutSession $checkoutSession;
    private ResultFactory $resultFactory;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSession $checkoutSession,
        ResultFactory $resultFactory,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId() && $order->getState() !== Order::STATE_CANCELED) {
            try {
                $order->registerCancellation(__('Payment failed or cancelled'))->save();
            } catch (\Exception $e) {
                $this->logger->error('SeerBit: Failed to cancel order', [
                    'order_id' => $order->getIncrementId(),
                    'error' => $e->getMessage()
                ]);
            }
        }
        $this->checkoutSession->restoreQuote();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout', ['_fragment' => 'payment']);
        return $resultRedirect;
    }
}
