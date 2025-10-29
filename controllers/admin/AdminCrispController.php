<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Crisp IM SAS
 *  @copyright 2024 Crisp IM SAS
 *  @license   All rights reserved to Crisp IM SAS
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;

class AdminCrispController extends ModuleAdminController
{
    public $module;

    public function __construct()
    {
        $crisp = Module::getInstanceByName('crisp');
        $this->module = $crisp;
        $this->bootstrap = true;
        $this->table = 'configuration';
        $this->className = 'Configuration';
        $this->lang = false;
        $this->display = 'view';

        parent::__construct();
    }

    public function displayAjaxEnableChatbox()
    {
        Configuration::updateValue('CRISP_CHATBOX_DISABLED', 0);
    }

    public function displayAjaxDisableChatbox()
    {
        Configuration::updateValue('CRISP_CHATBOX_DISABLED', 1);
    }

    public function displayAjaxEnableWebService()
    {
        // Delete any Webservice keys created by Crisp.
        $crisp_webservice_key_id = Configuration::get('CRISP_WEBSERVICE_KEY_ID');

        $webserviceKey = new WebserviceKey($crisp_webservice_key_id);
        if (Validate::isLoadedObject($webserviceKey)) {
            $webserviceKey->delete();
        }

        // Fetch new API key from URL
        $api_key = Tools::getValue('crisp_api_key');

        // Create new API key
        $apiAccess = new WebserviceKey();
        $apiAccess->key = $api_key;
        $apiAccess->description = 'Crisp - Used to find customers and their order details.';
        $apiAccess->save();

        // Enable webservice and save values.
        Configuration::updateValue('PS_WEBSERVICE', 1);
        Configuration::updateValue('CRISP_WEBSERVICE_KEY_ID', $apiAccess->id);

        // Set permissions for webservice key.
        $permissions = [
            'customers' => ['GET' => 1],
            'orders' => ['GET' => 1],
            'order_details' => ['GET' => 1],
            'carriers' => ['GET' => 1],
            'carts' => ['GET' => 1],
            'currencies' => ['GET' => 1],
            'products' => ['GET' => 1],
        ];

        WebserviceKey::setPermissionForAccount($apiAccess->id, $permissions);
    }

    public function displayAjaxToggleEnableWebservice()
    {
        // Disable any Webservice keys created by Crisp.
        $crisp_webservice_key_id = Configuration::get('CRISP_WEBSERVICE_KEY_ID');

        $webserviceKey = new WebserviceKey($crisp_webservice_key_id);
        if (Validate::isLoadedObject($webserviceKey)) {
            $webserviceKey->active = true;
            $webserviceKey->save();
        }
    }

    public function displayAjaxToggleDisableWebservice()
    {
        // Delete any Webservice keys created by Crisp.
        $crisp_webservice_key_id = Configuration::get('CRISP_WEBSERVICE_KEY_ID');

        $webserviceKey = new WebserviceKey($crisp_webservice_key_id);
        if (Validate::isLoadedObject($webserviceKey)) {
            $webserviceKey->active = false;
            $webserviceKey->save();
        }
    }

    public function renderView()
    {
        $link = new Link();
        $admin_url = $link->getAdminLink('AdminCrisp');
        $chatbox_disabled = Configuration::get('CRISP_CHATBOX_DISABLED');

        $http_callback = 'http' . (($_SERVER['SERVER_PORT'] == 443) ? 's://' : '://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $crisp_installed = false;
        $get_website_id = Tools::getValue('crisp_website_id');
        if (isset($get_website_id) && !empty($get_website_id)) {
            Configuration::updateValue('WEBSITE_ID', Tools::getValue('crisp_website_id'));
            $crisp_installed = true;
        }
        $api_key_invalid = false;
        $api_key_disabled = false;
        $crisp_api_key = Tools::getValue('crisp_api_key');
        $crisp_webservice_key_id = Configuration::get('CRISP_WEBSERVICE_KEY_ID');

        if (isset($crisp_api_key) && !empty($crisp_api_key)) {
            // If the key exists and it is disabled.
            if (WebserviceKey::keyExists($crisp_api_key)) {
                $api_key_disabled = !WebserviceKey::isKeyActive($crisp_api_key);
            } else {
                $api_key_invalid = true;
            }

            $webserviceKey = new WebserviceKey($crisp_webservice_key_id);
            // Consider api key disabled if new API key does not match existing API key.
            if (Validate::isLoadedObject($webserviceKey) && $webserviceKey->key !== $crisp_api_key) {
                $api_key_disabled = true;
                $api_key_invalid = true;
            }
        }

        if (!empty($crisp_webservice_key_id)) {
            $webserviceKey = new WebserviceKey($crisp_webservice_key_id);

            if (Validate::isLoadedObject($webserviceKey) && !$webserviceKey->active) {
                $api_key_disabled = true;
            }
        }

        $admin_locale = $this->context->language->iso_code;

        $context = Context::getContext();
        $shop = $context->shop;
        $shopName = Configuration::get('PS_SHOP_NAME');
        $shopDomain = $shop->domain;
        $adminEmail = '';
        $adminUsername = '';

        if (isset($context->employee) && $context->employee->id) {
            $adminUsername = $context->employee->firstname . ' ' . $context->employee->lastname;
            $adminEmail = $context->employee->email;
        }

        $website_id = Configuration::get('WEBSITE_ID');
        $is_crisp_working = !empty($website_id);
        $this->context->smarty->assign([
            'crisp_installed' => $crisp_installed,
            'is_crisp_working' => $is_crisp_working,
            'http_callback' => $http_callback,
            'website_id' => $website_id,
            'admin_url' => $admin_url,
            'admin_locale' => $admin_locale,
            'chatbox_disabled' => $chatbox_disabled,
            'api_key_disabled' => $api_key_disabled,
            'api_key_invalid' => $api_key_invalid,
            'crisp_api_key' => $crisp_api_key,
            'crisp_webservice_key_id' => $crisp_webservice_key_id,
            'crisp_plugin_identifier' => CRISP_PLUGIN_IDENTIFIER,
            'crisp_plugin_url' => CRISP_PLUGIN_URL,
            'crisp_plugin_source' => CRISP_PLUGIN_SOURCE,
            'logo' => '/modules/crisp/logo.png',
            'shop_name' => $shopName,
            'shop_domain' => $shopDomain,
            'user_email' => $adminEmail,
            'user_name' => $adminUsername,
        ]);

        $this->context->controller->addCSS(_PS_MODULE_DIR_ . 'crisp/views/css/style.css', 'all');
        $this->context->controller->addCSS('https://ui-kit.prestashop.com/backoffice/latest/css/bootstrap-prestashop-ui-kit.css', 'all');
        $this->context->controller->addJS('https://ui-kit.prestashop.com/backoffice/latest/js/prestashop-ui-kit.js', 'all');
        $this->context->controller->addJS(_PS_MODULE_DIR_ . 'crisp/views/js/petite-vue.js', 'all');
        $this->context->controller->addJS(_PS_MODULE_DIR_ . 'crisp/views/js/admin.js', 'all');
        $moduleManager = ModuleManagerBuilder::getInstance()->build();
        $accountsService = null;
        try {
            $accountsFacade = $this->module->getService('crisp.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            $accountsInstaller = $this->module->getService('crisp.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsFacade = $this->module->getService('crisp.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }
        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()->present($this->module->name),
            ]);

            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();

            return '';
        }
        if ($moduleManager->isInstalled('ps_eventbus')) {
            $eventbusModule = Module::getInstanceByName('ps_eventbus');
            $eventbusScope = [
                'info',
                'customers',
                'orders',
                'carriers',
                'carts',
                'currencies',
                'products',
            ];

            if (version_compare($eventbusModule->version, '1.9.0', '>=')) {
                $eventbusPresenterService = $eventbusModule->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');
                $this->context->smarty->assign('urlCloudsync', 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js');
                Media::addJsDef([
                    'contextPsEventbus' => $eventbusPresenterService->expose($this->module, $eventbusScope),
                ]);
            }
        }
        $moduleTemplatePath = _PS_MODULE_DIR_ . 'crisp/views/templates/admin/configure.tpl';
        $templateContent = $this->context->smarty->fetch($moduleTemplatePath);

        return $templateContent;
    }
}
