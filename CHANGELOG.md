# Changelog

All notable changes will be documented in this file

## 2.0.0 - 2026-06-26

### Added
- Magento 2.4.6 and 2.4.7 support (PHP 8.1+)
- CSP (Content Security Policy) whitelist for SeerBit checkout and API domains
- Server-side amount verification — paid amount is validated against order total
- Proper error logging throughout all payment operations
- SeerBit JS SDK v2 integration

### Changed
- Updated SeerBit API from v1 to v3 for payment verification
- Updated SeerBit API token endpoint to v2 with correct key format
- Replaced deprecated `Zend_Json::encode` with native `json_encode`
- Replaced deprecated `Magento\Payment\Model\Method\AbstractMethod` usage
- Replaced deprecated `Magento\Framework\App\Action\Action` with `HttpGetActionInterface` in RestoreCart controller
- Simplified `ConfigProvider` — removed unused dependencies
- Rewrote `Helper\Data` to extend `AbstractHelper` instead of `Magento\Payment\Helper\Data`
- Fixed `Block\SeerBitAssets` to use frontend `Template\Context` instead of backend context
- Load SeerBit JS SDK via layout XML `<head>` block instead of overriding global RequireJS config
- Use raw `curl_init()` for SeerBit API calls to avoid Magento Curl header conflicts
- Added store code to REST API URL for proper Magento webapi routing

### Fixed
- Fixed all namespaces
- Fixed missing CSRF plugin registration in `di.xml`
- Fixed webhook URL typo (`seetbit` → `seerbit_payment`)
- Fixed SeerBit dashboard link in webhook config
- Removed `setup_version` from `module.xml` (deprecated)

### Removed
- Removed `require_js.phtml` template override (replaced with layout XML approach)
- Removed `.DS_Store` files from repository

## 1.1.0 - 2020-10-08

- Bumped version

## 1.0.0

- Initial release
