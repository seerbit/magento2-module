<?php

namespace SeerBit\Payment\Model\Payment;

class Standard extends \Magento\Payment\Model\Method\AbstractMethod
{


    const CODE = 'seerbit_payment';
    const TOKEN_URL = 'https://seerbitapi.com/sbt/api/v1/auth';
    const VERIFY_TRANSACTION_URL = 'https://seerbitapi.com/sbt/api/card/v1/get/transaction/status/';

    protected $_code = self::CODE;
    protected $_isOffline = true;



    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
