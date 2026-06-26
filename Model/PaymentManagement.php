<?php

namespace Seerbit\Payment\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Seerbit\Payment\Model\Payment\Standard as SeerBitStandardModel;
use Psr\Log\LoggerInterface;

class PaymentManagement implements \Seerbit\Payment\Api\PaymentManagementInterface
{
    protected $seerbitPaymentInstance;
    protected $orderInterface;
    protected $checkoutSession;
    private $eventManager;
    private string $secretKey;
    private string $publicKey;
    private $seerBitHelper;
    private LoggerInterface $logger;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Seerbit\Payment\Helper\Data $seerBitHelper,
        LoggerInterface $logger
    ) {
        $this->eventManager = $eventManager;
        $this->seerBitHelper = $seerBitHelper;
        $this->logger = $logger;
        $this->seerbitPaymentInstance = $paymentHelper->getMethodInstance(SeerBitStandardModel::CODE);
        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;
        $this->secretKey = (string) $this->seerbitPaymentInstance->getConfigData('live_secret_key');
        $this->publicKey = (string) $this->seerbitPaymentInstance->getConfigData('live_public_key');
        if ($this->seerbitPaymentInstance->getConfigData('test_mode')) {
            $this->secretKey = (string) $this->seerbitPaymentInstance->getConfigData('test_secret_key');
            $this->publicKey = (string) $this->seerbitPaymentInstance->getConfigData('test_public_key');
        }
    }

    public function verifyPayment($reference)
    {
        try {
            $token = $this->getMerchantToken();
            if ($token) {
                return ['data' => $this->verifyTransaction($reference, $token)];
            }
            $this->seerBitHelper->restoreQuote();
            return ['data' => ['status' => 'server_error', 'message' => 'Error reaching SeerBit server. Please try again']];
        } catch (\Exception $exception) {
            $this->logger->error('SeerBit payment verification failed', [
                'reference' => $reference,
                'error' => $exception->getMessage()
            ]);
            $this->seerBitHelper->restoreQuote();
            return ['data' => ['status' => 'server_error', 'message' => 'Error reaching SeerBit server. Please try again']];
        }
    }

    private function getMerchantToken(): ?string
    {
        $ch = curl_init(SeerBitStandardModel::TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'key' => $this->secretKey . '.' . $this->publicKey
            ])
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->logger->info('SeerBit token response', ['status' => $httpCode, 'body' => $response]);

        if ($httpCode == 200) {
            $result = json_decode($response);
            if (isset($result->status) && $result->status === 'SUCCESS') {
                return $result->data->EncryptedSecKey->encryptedKey ?? null;
            }
        }
        return null;
    }

    private function verifyTransaction(string $reference, string $token): array
    {
        $ch = curl_init(SeerBitStandardModel::VERIFY_TRANSACTION_URL . $reference);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->logger->info('SeerBit verify response', [
            'reference' => $reference,
            'status' => $httpCode,
            'body' => $response
        ]);

        $result_body = json_decode($response);

        if ($httpCode == 200) {
            if (!isset($result_body->status)) {
                return ['status' => 'fail', 'message' => $result_body->message ?? 'Error reaching SeerBit server. Please try again'];
            }
            if ($result_body->status === 'SUCCESS') {
                $paidAmount = (float) ($result_body->data->payments->amount ?? 0);
                $order = $this->getOrder();

                if ($order) {
                    $orderAmount = (float) $order->getGrandTotal();

                    if ($paidAmount < $orderAmount) {
                        $this->logger->warning('SeerBit: Amount mismatch', [
                            'reference' => $reference,
                            'paid' => $paidAmount,
                            'expected' => $orderAmount
                        ]);
                        $this->seerBitHelper->restoreQuote();
                        return [
                            'status' => 'fail',
                            'message' => 'Payment amount ('. $paidAmount .') is less than order amount ('. $orderAmount .'). Please try again.'
                        ];
                    }
                }

                return [
                    'status' => 'success',
                    'message' => $result_body->data->message ?? 'Payment verified',
                    'data' => $result_body->data->payments ?? null
                ];
            }
        }
        return ['status' => 'server_error', 'message' => $result_body->message ?? 'Error reaching SeerBit server. Please try again'];
    }

    private function getOrder()
    {
        $lastOrder = $this->checkoutSession->getLastRealOrder();
        if ($lastOrder) {
            $lastOrderId = $lastOrder->getIncrementId();
        } else {
            return false;
        }
        if ($lastOrderId) {
            return $this->orderInterface->loadByIncrementId($lastOrderId);
        }
        return false;
    }
}
