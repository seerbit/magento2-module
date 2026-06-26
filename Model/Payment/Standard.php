<?php

namespace Seerbit\Payment\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;

class Standard extends AbstractMethod
{
    const CODE = 'seerbit_payment';
    const API_BASE_URL = 'https://seerbitapi.com/api';
    const TOKEN_URL = self::API_BASE_URL . '/v2/encrypt/keys';
    const VERIFY_TRANSACTION_URL = self::API_BASE_URL . '/v3/payments/query/';

    protected $_code = self::CODE;
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
