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
* @version   0.0.1
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Utanvetellenor extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'utanvetellenor';
        $this->tab = 'checkout';
        $this->version = '0.0.1';
        $this->author = 'rrd';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Utánvét Ellenőr');
        $this->description = $this->l('Check customers if they are ok to show COD');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Utánvét Ellenőr?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('UTANVETELLENOR_LIVE_MODE', false);

        if (!Configuration::get('UTANVETELLENOR_REFUSED_ORDERSTATE')) {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = $this->l('Package Refused');
            }
            $orderState->send_email = false;
            $orderState->color = '#AC448A';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = $this->name;
            if ($orderState->add()) {
                copy(
                    dirname(__FILE__).'/views/img/utanvet_ellenor_logo.gif',
                    dirname(__FILE__).'/../../img/os/'.(int) $orderState->id.'.gif'
                );
            }
            Configuration::updateValue('UTANVETELLENOR_REFUSED_ORDERSTATE', (int) $orderState->id);
        }
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionOrderStatusUpdate');
    }

    public function uninstall()
    {
        Configuration::deleteByName('UTANVETELLENOR_LIVE_MODE');

        $orderState = new OrderState((int) Configuration::get('UTANVETELLENOR_REFUSED_ORDERSTATE'));
        if ($orderState->id) {
            $orderState->delete();
        }


        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitUtanvetellenorModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUtanvetellenorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'UTANVETELLENOR_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Live or Sandbox mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Public API Key generated at https://utanvet-ellenor.hu/'),
                        'name' => 'UTANVETELLENOR_PUBLIC_KEY',
                        'label' => $this->l('Public API Key'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Private API Key generated at https://utanvet-ellenor.hu/'),
                        'name' => 'UTANVETELLENOR_PRIVATE_KEY',
                        'label' => $this->l('Private API Key'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-star"></i>',
                        'desc' => $this->l('Calculated with the following formula: (good-bad) / all, so a 0.5 reputation can mean 6 successful and 2 rejected deliveries.'),
                        'name' => 'UTANVETELLENOR_THRESHOLD',
                        'label' => $this->l('Reputation threshold'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'UTANVETELLENOR_LIVE_MODE' => Configuration::get('UTANVETELLENOR_LIVE_MODE', false),
            'UTANVETELLENOR_PUBLIC_KEY' => Configuration::get('UTANVETELLENOR_PUBLIC_KEY', null),
            'UTANVETELLENOR_PRIVATE_KEY' => Configuration::get('UTANVETELLENOR_PRIVATE_KEY', null),
            'UTANVETELLENOR_THRESHOLD' => Configuration::get('UTANVETELLENOR_THRESHOLD', 0,5),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionOrderStatusUpdate($params)
    {
        // TODO add configure option: which status is payed?
        // if ($params['newOrderStatus']->id == $payedStatus) {
            // sned the info the the API
        //}
    }
}
