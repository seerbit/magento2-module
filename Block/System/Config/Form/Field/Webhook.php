<?php

namespace Seerbit\Payment\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\Store as Store;

/**
 * Backend system config datetime field renderer
 *
 * @api
 * @since 100.0.2
 */
class Webhook extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Store $store
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Store $store,
        array $data = []
    ) {
        $this->store = $store;

        parent::__construct($context, $data);
    }

    /**
     * Returns element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $webhookUrl = $this->store->getBaseUrl() . 'seetbit/payment/webhook';
        $value = "You may login to <a target=\"_blank\" href=\"https://dashboard.seerbitapi.com/#/settings\">SeerBit Settings</a> to update your Webhook URL to:<br><br>"
                . "<strong style='color:red;'>$webhookUrl</strong>";

        $element->setValue($webhookUrl);

        return $value;
    }
}
