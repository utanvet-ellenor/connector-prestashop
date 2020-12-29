{*
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
*}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Utánvét Ellenőr' mod='utanvetellenor'}</h3>
	<p>
		<strong>{l s="Check your customer's reputation for Cash on Delivery" mod='utanvetellenor'}</strong><br />
		{l s="Customers with a reputation below the threshold will not be able to select Cash on Delivery as their preferred payment method." mod='utanvetellenor'}<br />
	</p>
	<br />
	<p>
		{l s="This module helps you minimize the risk of sending out packages resulting in unsuccessful deliveries." mod='utanvetellenor'}
	</p>
</div>

<div class="panel">
	<h3><i class="icon icon-cogs"></i> {l s='Override' mod='utanvetellenor'}</h3>
		{if $overrideExists}
			<p class="alert alert-success">
				{l s='Override install is OK' mod='utanvetellenor'}
			</p>
		{else}
			<p class="alert alert-danger">
				{l s='ERROR: Override is not installed' mod='utanvetellenor'}
			</p>
		{/if}
</div>

{if $update}
<div class="panel">
	<h3><i class="icon icon-cogs"></i> {l s='Update' mod='utanvetellenor'}</h3>
	<p class="alert alert-danger">
		{l s='Update available' mod='utanvetellenor'} <a href="{$update->download_url}">{$update->version} ({$update->last_updated})</a>
	</p>
</div>
{/if}
