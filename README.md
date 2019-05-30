# B2BinPay Crypto Payment Gateway for Magento 2

Accept Bitcoin, Bitcoin Cash, Litecoin, Ethereum, and other CryptoCurrencies via B2BinPay on your Magento store.

[![Build Status](https://scrutinizer-ci.com/g/b2binpay/magento2/badges/build.png?b=master)](https://scrutinizer-ci.com/g/b2binpay/magento2/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/b2binpay/magento2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/b2binpay/magento2/?branch=master)

## Description

[B2BinPay](https://b2binpay.com/) is an all-in-one global solution for CryptoCurrency Payments.

We strive to make Digital Payments Simple, Secure and Quick, improving financial access through innovative technology developed in-house.

B2BinPay allows any business to securely and cost-effectively Send, Receive, Store, Exchange and Accept CryptoCurrency Payments online.

We deliver Fast, Simple and Efficient financial services technology that unlocks access to CryptoCurrency Markets.

### Our Fees

We are proud to provide one of the best CryptoCurrency Payment Solution with the Lowest Fees in the industry! Accept Money Across Borders for as low as 0.5%.

### Available CryptoCurrencies

Bitcoin, Bitcoin Cash, Ethereum, DASH, Litecoin, Monero, NEO, NEM, Ripple, B2BX and any ERC20, NEO tokens, Cardano in one place!

## Installation

If you don’t already have one, create a [B2BinPay](https://b2binpay.com/) account.

1. Update the `composer.json` file in the root directory of your Magento store and wait for Composer to finish updating the dependencies.
```
composer require b2binpay/magento2
```
For Magento 2.2:
```
composer require b2binpay/magento2:1
```
2. Enable the extension and clear the static view files.
```
bin/magento module:enable B2Binpay_Payment --clear-static-content
```
3. Register the extension and initiate the database migrations.
```
bin/magento setup:upgrade
```
4. Recompile the Magento project.
```
bin/magento setup:di:compile
```
5. Clear the Magento store’s cache.
```
bin/magento cache:flush
```

### Configuration

1. Navigate to Stores → Configuration → Sales → Payment Methods → B2BinPay in your Magento backend.
2. Enter the Auth API Key / Auth API Secret, then fill your B2BinPay Wallet IDs and press Check Auth button to verify.
3. Save config. The setup is now finished. You should see the payment methods in your checkout.
