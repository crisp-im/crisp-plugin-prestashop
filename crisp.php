<?php
/**
 * Crisp Module
 *
 * @author    Crisp IM SAS
 * @copyright 2026 Crisp IM SAS
 * @license   All rights reserved to Crisp IM SAS
 * @version 1.3.1
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

class Crisp extends Module
{
    /**
     * Webservice resources Crisp reads to find customers and their orders.
     */
    public const WEBSERVICE_PERMISSIONS = [
        'customers' => ['GET' => 1],
        'orders' => ['GET' => 1],
        'order_details' => ['GET' => 1],
        'order_carriers' => ['GET' => 1],
        'order_states' => ['GET' => 1],
        'order_slip' => ['GET' => 1],
        'carriers' => ['GET' => 1],
        'carts' => ['GET' => 1],
        'currencies' => ['GET' => 1],
        'products' => ['GET' => 1],
        'combinations' => ['GET' => 1],
    ];

    public $useLightMode;
    public $page;
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'crisp';
        $this->tab = 'administration';
        $this->author = 'Crisp IM';
        $this->version = '1.3.1';
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
        $this->description = $this->l("Improve customer support with Crisp: for each conversation, you get customer's orders data from Prestashop.");
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayHeader') && $this->registerHook('displayBackOfficeHeader') && $this->installTab();
    }

    public function uninstall()
    {
        // Delete Webservice keys created by Crisp, for every shop context (multistore).
        $crisp_webservice_key_ids = Db::getInstance()->executeS(
            'SELECT DISTINCT value FROM ' . _DB_PREFIX_ . 'configuration WHERE name = "CRISP_WEBSERVICE_KEY_ID"'
        );

        foreach ($crisp_webservice_key_ids ?: [] as $crisp_webservice_key_id) {
            $webserviceKey = new WebserviceKey((int) $crisp_webservice_key_id['value']);
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

    /**
     * Grants the current webservice permissions to the webservice keys created by Crisp, for every
     * shop context (multistore). Lets merchants get new permissions by updating the module.
     */
    public function ensureWebservicePermissions()
    {
        $crisp_webservice_key_ids = Db::getInstance()->executeS(
            'SELECT DISTINCT value FROM ' . _DB_PREFIX_ . 'configuration WHERE name = "CRISP_WEBSERVICE_KEY_ID"'
        );

        foreach ($crisp_webservice_key_ids ?: [] as $crisp_webservice_key_id) {
            $webserviceKey = new WebserviceKey((int) $crisp_webservice_key_id['value']);

            if (Validate::isLoadedObject($webserviceKey)) {
                WebserviceKey::setPermissionForAccount((int) $webserviceKey->id, self::WEBSERVICE_PERMISSIONS);
            }
        }

        return true;
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

        if (!$website_id || $chatbox_disabled) {
            return '';
        }

        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-crisp-script',
            'modules/' . $this->name . '/js/lib/hook.js'
        );

        $this->context->smarty->assign([
            'crisp_website_id' => $website_id,
            'crisp_plugin_url' => CRISP_PLUGIN_URL,
            'crisp_context_url' => $this->context->link->getModuleLink(
                $this->name,
                'context',
                ['ajax' => 1],
                true
            ),
        ]);

        return $this->display(__FILE__, 'crisp.tpl');
    }

    public function hookDisplayBackOfficeHeader($params = [])
    {
        Configuration::get("PS_ALLOW_HTML_\x49FRAME") or Configuration::updateValue("PS_ALLOW_HTML_\x49FRAME", 1);

        return $this->context->smarty->fetch(_CRISP_PATH_ . '/views/templates/hook/backoffice_header.tpl');
    }
}
