<?php

namespace Seerbit\Payment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class SeerBitAssets extends Template
{
    private AssetRepository $assetRepo;

    public function __construct(
        Template\Context $context,
        AssetRepository $assetRepo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->assetRepo = $assetRepo;
    }

    public function getAssets(): array
    {
        return [
            'seerbit_logo' => $this->assetRepo->getUrlWithParams(
                'Seerbit_Payment::images/seerbit-logo.svg',
                ['area' => 'frontend']
            )
        ];
    }
}
