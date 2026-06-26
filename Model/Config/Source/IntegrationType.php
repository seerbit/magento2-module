<?php

namespace Seerbit\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IntegrationType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'inline', 'label' => __('Inline - (Popup)')],
            ['value' => 'standard', 'label' => __('Standard - (Redirect)')]
        ];
    }

    public function toArray(): array
    {
        return [
            'inline' => __('Inline - (Popup)'),
            'standard' => __('Standard - (Redirect)')
        ];
    }
}
