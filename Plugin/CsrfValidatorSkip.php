<?php

namespace Seerbit\Payment\Plugin;

use Magento\Framework\App\Request\CsrfValidator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ActionInterface;

class CsrfValidatorSkip
{
    public function aroundValidate(
        CsrfValidator $subject,
        \Closure $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        if ($request->getModuleName() === 'seerbit_payment') {
            return;
        }
        $proceed($request, $action);
    }
}
