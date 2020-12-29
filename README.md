# Utánvét Ellenőr integration for PrestaShop

Connect your PrestaShop webshop to our SaaS available at https://utanvet-ellenor.hu.
[Register](https://utanvet-ellenor.hu/register) to obtain API keys.

## Requirements

This module was tested on PrestaShop 1.7.7.0 and requires at least PHP 7.0.

## Installation

1. [Register](https://utanvet-ellenor.hu/register) to obtain API keys, then set them in the module's settings.
2. Download the package from the releases tab of this repository.
3. Navigate to Modules > Module Manager.
4. Click on `Upload a module`.
5. Drop or select the previously downloaded ZIP archive.
6. Configure the plugin: 
    1. Set your API keys.
    2. Set your preferred threshold.
    3. Select which order statuses should trigger the feedback to our service.
    4. Select which payment methods should be hidden if the user's reputation is below the set threshold. 

## Overview

Utánvét Ellenőr is a SaaS provided by Dro-IT Ltd from Hungary: a service which will let shop owners filter orders with Cash on Delivery coming from known fraudulent e-mail addresses.

### How does it work?

The idea behind the service is the following:
* Someone orders with Cash on Delivery payment method, but later refuses to accept the package from the courier.
* The shop owner flags this order with the `Refused Package` order status.
* The module listens for orders entering this status.
* Once an order ends up in this status, the module will hash the e-mail address of the user on the shop server with SHA256 and sends the hash to our service, accompanied by a `-1`.
* If the courier could hand over the package successfully, the shop owner flags the order with the successful order status. In this case the module hashes the e-mail with the same SHA256 and sends the hash to our service, accompanied by a `+1`.
* When someone with the same e-mail address would like to order (from the same or from another shop), this module can disable Cash on Delivery from available payment methods:
    * The user enters his e-mail address.
    * This value gets hashed with the same SHA256 algorithm and the module asks our service about this hash.
    * The service will return a JSON array and if the e-mail reputation provided in this payload does not meet the minimum value set by the shop owner in the module settings (`Reputation threshold`), the module will disable the selected payment methods.

### Privacy implications

All inputs are hashed with SHA-256 by the module on your server. This means:
* The entered e-mail address will NEVER leave your system.
* SHA-256 is considered to be safe for hashing.
* On "check requests" we don't receive the e-mail address, just a hash, and we provide only a couple of "numbers" about that hash. There is no way for us to know what was the original string before hashing.
* In order to use our services, you MUST notify your users that "Automated individual decision-making" might be applied during checkout. For more information, please see GDPR Art. 22.

> Note: this is not a legal advice. Consult your attourney before using this service in production.

## Found a bug?

Check the issues or [open a new one](https://github.com/rrd108/ps-utanvet-ellenor/issues)!

Brought to you by [Rādhārādhya dāsa](https://webmania.cc/) & [dr. Ottó Radics](https://www.webmenedzser.hu)
