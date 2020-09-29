<?php
namespace Seerbit\Payment\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Seerbit\Payment\Model\Payment\Standard as SeerBitStandardModel;

class PaymentManagement implements \Seerbit\Payment\Api\PaymentManagementInterface
{
    protected $seerbitPaymentInstance;

    protected $orderInterface;
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;
    private $secretKey;
    private $publicKey;
    private $curl;
    private $seerBitHelper;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Seerbit\Payment\Helper\Data $seerBitHelper
    ) {
        $this->eventManager = $eventManager;
        $this->curl = $curl;
        $this->seerBitHelper = $seerBitHelper;
        $this->seerbitPaymentInstance = $paymentHelper->getMethodInstance(SeerBitStandardModel::CODE);

        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;

        $this->secretKey = $this->seerbitPaymentInstance->getConfigData('live_secret_key');
        $this->publicKey = $this->seerbitPaymentInstance->getConfigData('live_public_key');
        if ($this->seerbitPaymentInstance->getConfigData('test_mode')) {
            $this->secretKey = $this->seerbitPaymentInstance->getConfigData('test_secret_key');
            $this->publicKey = $this->seerbitPaymentInstance->getConfigData('test_public_key');
        }
    }

    /**
     * @param string $reference
     * @return array
     */
    public function verifyPayment($reference)
    {
        try {
            $getToken = $this->getMerchantToken();
            if ($getToken['status'] === 'success') {
                return ['data' => $this->verifyTransaction($reference, $getToken['token'])];
            }
            $this->seerBitHelper->restoreQuote();
            return ['data' => ['status' => 'server_error', 'message' => 'Error reaching SeerBit server. Please try again'] ];
        } catch (\Exception $exception) {
            $this->seerBitHelper->restoreQuote();
            return ['data' => ['status' => 'server_error', 'message' => 'Error reaching SeerBit server. Please try again'] ];
        }
    }

    private function getMerchantToken()
    {
        //SETUP CURL
        $this->curl->addHeader("Content-Type", "application/json");

        //BUILD TOKEN REQUEST PAYLOAD
        $params = json_encode(
            ['clientSecret' => hash("sha256", $this->publicKey . "." . $this->secretKey),
                'clientId' =>$this->publicKey]
        );

        //GET MERCHANT TOKEN
        $this->curl->post(SeerBitStandardModel::TOKEN_URL, $params);

        //RESULT OF TOKEN REQUEST
        $result_body = json_decode($this->curl->getBody());
        $result_status = $this->curl->getStatus();

        //VALIDATE RESPONSE AND GET MERCHANT TOKEN
        if ($result_status == 200) {
            if ($result_body->responseCode === "00") {
                return ['token' => $result_body->access_token, 'status' => 'success'];
            }
            return ['status' => 'fail', 'message' => $result_body->message];
        }
        return [ 'status' => 'server_error', 'message' => 'Error reaching SeerBit servers'];
    }

    private function verifyTransaction($reference, $token)
    {
        //SETUP CURL
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $token);

        //GET MERCHANT TOKEN
        $this->curl->get(SeerBitStandardModel::VERIFY_TRANSACTION_URL . $reference);

        //RESULT OF TOKEN REQUEST
        $result_body = json_decode($this->curl->getBody());
        $result_status = $this->curl->getStatus();
        //VALIDATE RESPONSE AND GET MERCHANT TOKEN
        if ($result_status == 200) {
            if (!isset($result_body->code)) {
                return ['status' => 'fail', 'message' => isset($result_body->message) ? $result_body->message : 'Error reaching SeerBit server. Please try again'];
            }
            if ($result_body->code === "00") {
                return ['status' => 'success', 'message' => $result_body->message, 'data' => $result_body->transaction];
            }
        }
        return [ 'status' => 'server_error', 'message' => isset($result_body->message) ? $result_body->message : 'Error reaching SeerBit server. Please try again'];
    }

    /**
     * Loads the order based on the last real order
     * @return boolean
     */
    private function getOrder()
    {
        // get the last real order id
        $lastOrder = $this->checkoutSession->getLastRealOrder();
        if ($lastOrder) {
            $lastOrderId = $lastOrder->getIncrementId();
        } else {
            return false;
        }

        if ($lastOrderId) {
            // load and return the order instance
            return $this->orderInterface->loadByIncrementId($lastOrderId);
        }
        return false;
    }


}
