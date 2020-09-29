<?php
namespace Seerbit\Payment\Api;

/**
 * PaymentManagementInterface
 *
 * @api
 */
interface PaymentManagementInterface
{
    /**
     * @param string $reference
     * @return array
     */
    public function verifyPayment($reference);
}
