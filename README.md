<div align="center">
 <img width="400" valign="top" src="https://assets.seerbitapi.com/images/seerbit_logo_type.png">
</div>

<h1 align="center">
  <img width="60" valign="bottom" src="https://devdocs.magento.com/assets/i/adobe-a.svg">
   SeerBit
</h1>

# SeerBit Payment Module for Magento 2

Accept credit/debit cards, bank transfers, mobile money, and other payment methods on your Magento 2 store with SeerBit.

## Requirements

- **Magento** 2.4.6 or higher
- **PHP** 8.1 or higher

## Installation

Via [Composer](https://getcomposer.org):

```bash
composer require seerbit/magento2-module
```

Then enable the module:

```bash
php bin/magento module:enable Seerbit_Payment --clear-static-content
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

## Configuration

1. Log in to your **Magento Admin Panel**
2. Navigate to **Stores > Configuration > Sales > Payment Methods**
3. Expand **SeerBit Payment Gateway**
4. Set **Enabled** to *Yes*
5. Enter your **Test/Live Public Key** and **Secret Key**
6. Set **Test Mode** to *Yes* for sandbox testing, *No* for live transactions
7. Click **Save Config**

You can find your API keys in the [SeerBit Merchant Dashboard](https://www.dashboard.seerbitapi.com) under **Settings > API Keys**.

## Features

- Supports cards, bank transfers, mobile money, and USSD payments
- Test and live mode support
- CSP (Content Security Policy) compliant
- Server-side payment verification via SeerBit API v3
- Automatic order status updates on successful payment
- Cart restoration on failed/cancelled payments

## Supported Magento Versions

- Magento 2.4.6
- Magento 2.4.7 (including patch releases)

## API Documentation

- [SeerBit API Reference](https://seerbit.github.io/openapi/)
- [SeerBit Developer Docs](https://doc.seerbit.com/)

## License

[MIT](LICENSE)
