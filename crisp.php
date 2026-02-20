<?php
/**
 * Crisp Module
 *
 * @author    Crisp IM SAS
 * @copyright 2026 Crisp IM SAS
 * @license   All rights reserved to Crisp IM SAS
 * @version 1.2.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

define('_CRISP_PATH_', _PS_MODULE_DIR_ . 'crisp/');
define('CRISP_PLUGIN_ID', '100c4d82-e362-4eb3-a31a-e8a26e59b8fa');
define('CRISP_PLUGIN_KEY', '7274ce7042a02178d0df4a071c31f2ee981406ae317be4a56ef8c22cad02c143');
define('CRISP_APP_URL', 'https://app.crisp.chat');
define('CRISP_PLUGIN_URL', 'https://plugins.crisp.chat/urn:crisp.im:prestashop:0');
define('CRISP_PLUGIN_IDENTIFIER', 'be40c894-22bb-408c-8fdc-aafb5e6b1985');
define('CRISP_PLUGIN_SOURCE', 'github');

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use Prestashop\ModuleLibMboInstaller\Installer;
use Prestashop\ModuleLibMboInstaller\Presenter;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class Crisp extends Module
{
    private $container;
    public $useLightMode;
    public $page;
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'crisp';
        $this->tab = 'administration';
        $this->author = 'Crisp IM';
        $this->version = '1.2.0';
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->page = basename(__FILE__, '.php');
        $this->bootstrap = true;
        $this->need_instance = 1;
        $this->useLightMode = true;
        $this->module_key = 'cc67e1a6e3a327f43ecc8037cd7f459e';

        parent::__construct();

        $this->displayName = $this->l('Crisp - Live chat & AI Chatbot');
        $this->description = $this->l("Improve customer support with Crisp: for each conversation, you get customer's orders data synced from Prestashop.");

        if ($this->container === null) {
            $this->container = new ServiceContainer(
                $this->name,
                _CRISP_PATH_
            );
        }
    }

    public function install()
    {
        $mboStatus = (new Presenter())->present();
        if (!$mboStatus['isInstalled']) {
            try {
                $mboInstaller = new Installer(_PS_VERSION_);
                $result = $mboInstaller->installModule();
                $this->installDependencies();
            } catch (Exception $e) {
                $this->context->controller->errors[] = $e->getMessage();
                return 'Error during MBO installation';
            }
        } else {
            $this->installDependencies();
        }

        return parent::install() && $this->registerHook('displayHeader') && $this->registerHook('displayBackOfficeHeader') && $this->installTab();
    }

    public function uninstall()
    {
        // Delete Webservice keys created by Crisp.
        $crisp_webservice_key_id = Configuration::get('CRISP_WEBSERVICE_KEY_ID');

        if ($crisp_webservice_key_id) {
            $webserviceKey = new WebserviceKey((int) $crisp_webservice_key_id);
            if (Validate::isLoadedObject($webserviceKey)) {
                $webserviceKey->delete();
            }
        }

        // Delete Crisp Configurations
        Configuration::deleteByName('CRISP_WEBSERVICE_KEY_ID');
        Configuration::deleteByName('CRISP_CHATBOX_DISABLED');
        Configuration::deleteByName('WEBSITE_ID');

        return parent::uninstall() && $this->uninstallTab();
    }

    public function installDependencies()
    {
        $moduleManager = ModuleManagerBuilder::getInstance()->build();

        /* PS Account */
        if (!$moduleManager->isInstalled('ps_accounts')) {
            $moduleManager->install('ps_accounts');
        } elseif (!$moduleManager->isEnabled('ps_accounts')) {
            $moduleManager->enable('ps_accounts');
        }

        /* Cloud Sync - PS Eventbus */
        if (!$moduleManager->isInstalled('ps_eventbus')) {
            $moduleManager->install('ps_eventbus');
        } elseif (!$moduleManager->isEnabled('ps_eventbus')) {
            $moduleManager->enable('ps_eventbus');
        }
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = 'AdminCrisp';
        $tab->name = [];
        $tab->icon = 'chat';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Crisp';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdmin');
        $tab->module = $this->name;

        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int) Db::getInstance()->getValue(
            'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminCrisp"'
        );
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    public function getContent()
    {
        $link = $this->context->link;

        if (null == $link) {
            throw new PrestaShopException('Link is null');
        }

        Tools::redirectAdmin(
            $link->getAdminLink('AdminCrisp', true, [])
        );
    }

    public function hookDisplayHeader($params)
    {
        $website_id = Configuration::get('WEBSITE_ID');
        $chatbox_disabled = Configuration::get('CRISP_CHATBOX_DISABLED');

        if ($website_id && !$chatbox_disabled) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-crisp-script',
                'modules/' . $this->name . '/js/lib/hook.js'
            );
        }

        if (is_null($this->context->cart) || is_null($this->context->cart->id)) {
            $cartId = null;
            $currencyId = null;
            $products = null;
        } else {
            $cartId = $this->context->cart->id;
            $currencyId = $this->context->cart->id_currency;
            $products = $this->context->cart->getProducts();
        }
        $productsData = [];

        if (!is_null($products)) {
            foreach ($products as $key => $product) {
                $productsData[$key] = ['id_product' => (int) $product['id_product'], 'id_product_attribute' => (int) $product['id_product_attribute'], 'quantity' => (int) $product['quantity'], 'price' => (float) $product['price']];
            }
        }

        $customer = $this->context->customer;
        $customerAddress = '';
        $customerPhone = '';
        if ($customer->isLogged()) {
            $addresses = $customer->getAddresses($this->context->language->id);
            if (!empty($addresses)) {
                $address = $addresses[0];
                $customerAddress = $address['address1'] . ' ' . $address['address2'] . ', ' . $address['postcode'] . ' ' . $address['city'] . ', ' . $address['country'];
                $customerPhone = $address['phone'];
            }
        }

        $this->context->smarty->assign([
            'crisp_customer' => $customer,
            'crisp_customer_address' => $customerAddress,
            'crisp_customer_phone' => $customerPhone,
            'crisp_website_id' => $website_id,
            'crisp_chatbox_disabled' => $chatbox_disabled,
            'cartId' => $cartId,
            'currencyId' => $currencyId,
            'productsData' => json_encode($productsData),
            'crisp_plugin_url' => CRISP_PLUGIN_URL,
        ]);

        return $this->display(__FILE__, 'crisp.tpl');
    }

    public function hookDisplayBackOfficeHeader($params = [])
    {
        Configuration::get("PS_ALLOW_HTML_\x49FRAME") or Configuration::updateValue("PS_ALLOW_HTML_\x49FRAME", 1);

        return $this->context->smarty->fetch(_CRISP_PATH_ . '/views/templates/hook/backoffice_header.tpl');
    }
}
