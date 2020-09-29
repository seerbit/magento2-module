<?php
namespace Seerbit\Payment\Block;

use Magento\Framework\View\Asset\Repository as AssetRepository;

class SeerBitAssets extends \Magento\Framework\View\Element\Template
{
    protected $assetRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        AssetRepository $assetRepository
    ) {
        $this->assetRepository = $assetRepository;
        parent::__construct($context);
    }

    public function getAssets()
    {
        $output['seerbit_logo'] = $this->getViewFileUrl('Seerbit_Payment::images/seerbit-logo.svg');

        return $output;
    }

    public function getViewFileUrl($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->getRequest()->isSecure()], $params);
        return $this->assetRepository->getUrlWithParams($fileId, $params);
    }
}
