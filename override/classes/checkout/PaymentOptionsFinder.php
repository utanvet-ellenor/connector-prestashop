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
* @version   0.0.2
*/

use webmenedzser\UVBConnector\UVBConnector;

class PaymentOptionsFinder extends PaymentOptionsFinderCore
{
    public function find()
    {
        $paymentOptions = parent::find();

        $customer = Context::getContext()->customer;
        $connector = new UVBConnector($customer->email, Configuration::get('UTANVETELLENOR_PUBLIC_KEY'), Configuration::get('UTANVETELLENOR_PRIVATE_KEY'), Configuration::get('UTANVETELLENOR_LIVE_MODE'));
        $connector->threshold = Configuration::get('UTANVETELLENOR_THRESHOLD');
        $reputation = json_decode($connector->get());

        if($reputation->message->totalRate < Configuration::get('UTANVETELLENOR_THRESHOLD')) {
            $filteredPaymentOptions = [];
            foreach($paymentOptions as $module => $paymentOption) {
                if(stripos($module, 'cod') === false) {
                    $filteredPaymentOptions[$module] = $paymentOption;
                }
            }
            return $filteredPaymentOptions;
        }

        return $paymentOptions;
    }
}