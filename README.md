<div align="center">
 <img width="200" valign="top" src="https://res.cloudinary.com/dy2dagugp/image/upload/v1571249658/seerbit-logo_mdinom.png">
</div>

<h1 align="center">
  <img width="60" valign="bottom" src="https://devdocs.magento.com/assets/i/adobe-a.svg">
   SeerBit
</h1>

# Seerbit Payment module for Magento 2

# Requirements
This module was built and tested using Magento 2(2.3.5) and framework 102.0.5 
PHP 7.2.20 or higher

## Installation

Via [composer](https://getcomposer.org). Follow the composer
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.


Once composer is installed, execute the following commands in your project root to install this library:

```bash
composer require seerbit/magento2-module
```

* Wait while dependencies are updated.

* Enter following commands to enable module:

```bash
php bin/magento module:enable Seerbit_Payment --clear-static-content
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

## Usage
* If you don't have one, register a merchant account on [SeerBit Merchant Dashboard](https://dashboard.seerbitapi.com/#/auth/register) 
* Enable and configure `SeerBit` in *Magento Admin* under `Stores/Configuration/Payment` Methods
* You can find both public and secret keys from your merchant dashboard -> Account menu -> Settings -> API Keys. 

## API Documentation ##
* https://doc.seerbit.com/
