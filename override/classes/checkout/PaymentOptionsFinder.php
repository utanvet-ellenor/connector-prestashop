<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    rrd <rrd@webmania.cc>
* @copyright 2020 rrd
* @license   http://opensource.org/licenses/afl-3.0.php
* @version   0.0.3
*/

use webmenedzser\UVBConnector\UVBConnector;

class PaymentOptionsFinder extends PaymentOptionsFinderCore
{
    public function find()
    {
        $paymentOptions = parent::find();
        $customer = Context::getContext()->customer;
        $email = $customer->email ?? null;
        if (!$email) {
            return;
        }

        /**
         * Initialize UVB Connector
         */
        $publicApiKey = Configuration::get('UTANVETELLENOR_PUBLIC_KEY');
        $privateApiKey = Configuration::get('UTANVETELLENOR_PRIVATE_KEY');
        $production = Configuration::get('UTANVETELLENOR_LIVE_MODE');
        $threshold = Configuration::get('UTANVETELLENOR_THRESHOLD');
        $paymentMethodsToHide = Configuration::get('UTANVETELLENOR_PAYMENT_METHODS_TO_HIDE');

        /**
         * If there are no payment methods to be hidden, return instantly.
         */
        if (!$paymentMethodsToHide && !count($paymentMethodsToHide) < 1) {
            return $paymentOptions;
        }

        /**
         * If no API keys are set, return.
         */
        if (!$publicApiKey || !$privateApiKey) {
            return $paymentOptions;
        }

        $connector = new UVBConnector(
            $email,
            $publicApiKey,
            $privateApiKey,
            $production
        );

        $connector->threshold = $threshold;
        $reputation = json_decode($connector->get());

        /**
         * If reputation is above the threshold, return all payment options.
         */
        if ($threshold < $reputation->message->totalRate) {
            return $paymentOptions;
        }

        /**
         * If not, filter out all Cash on Delivery payment methods.
         */
        $filteredPaymentOptions = [];
        $paymentMethodsToHide = explode(',', str_replace(' ', '', $paymentMethodsToHide));
        foreach ($paymentOptions as $module => $paymentOption) {
            if (in_array($module, $paymentMethodsToHide)) {
                unset($paymentOptions[$module]);
            }
        }

        return $paymentOptions;
    }
}
