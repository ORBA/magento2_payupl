# Orba Magento 2 Payu.pl Module

Payu.pl integration for Magento 2

## Build status

[![Build Status](https://dev.azure.com/michalbiarda/Orba%20Magento%202%20extensions/_apis/build/status/ORBA.magento2_payupl?branchName=dev)](https://dev.azure.com/michalbiarda/Orba%20Magento%202%20extensions/_build/latest?definitionId=2&branchName=dev)

## Supported Magento versions

- Open Source 2.2
- Open Source 2.3

## Key features

- both POS types support (classic and checkout),
- multi-store support,
- full integration with Magento payment flow (transactions, refunds, etc.),
- logging all APIs exceptions and errors,
- possibility to choose payment type directly in checkout (only for classic POS),
- possibility to repeat unsuccessful payment,
- over 300 unit tests

## Configuration in Payu.pl panel

You have to create new POS in Payu.pl panel for your Magento store. Both "classic" and "checkout" POS types are supported but we recommend to use "classic".

POS should have "Data coding" set to "UTF-8".

"Error return address" should be set to "yourdomain/orba_payupl/payment/end/error/%error%/session_id/%sessionId%"

"Successful return address" should be set to "yourdomain/orba_payupl/payment/end"

"Address for reports" should be set to "yourdomain/orba_payupl/payment/notify"

The string "yourdomain" should be replaced with your store domain, eg. "magento-store.pl/orba_payupl/payment/end".

For testing purposes enable "test payment". It's a fake payment method you can use during checkout. You won't be charged for anything but all the processes will work just like if it was a normal payment.

## Configuration in Magento panel

The configuration can be found in Stores > Configuration > Sales > Payment Methods > ORBA | Payu.pl. It should be pretty straight-forward.