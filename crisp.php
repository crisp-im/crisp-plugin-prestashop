<?php
/**
* Crisp Module
*
* @author    Baptiste Jamin <baptiste@crisp.chat>
* @copyright Crisp IM 2014
* @license
* @version   Release: $Revision: 0.3.4 $
*/

class Crisp extends Module
{

    public function __construct()
    {
        $this->name = $this->l('crisp');
        $this->displayName = $this->l('Crisp Livechat');
        $this->author = 'Crisp IM';
        $this->tab = 'front_office_features';
        $this->version = "0.3.3";
        $this->module_key = 'cc67e1a6e3a327f43ecc8037cd7f459e';
        $this->page = basename(__FILE__, '.php');
        $this->bootstrap = true;
        $this->description =
            $this->l('Crisp is the best livechat to interact with customers.');

        parent::__construct();
    }

    public function install()
    {
        return (parent::install() && $this->registerHook('displayHeader') && $this->installTab());
    }

    public function uninstall()
    {
        return(parent::uninstall() == false && $this->uninstallTab());
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminCrisp';
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Crisp';
        }

        $tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');
        $tab->module = $this->name;
        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminCrisp');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    public function hookDisplayHeader($params)
    {
        $website_id = Configuration::get('WEBSITE_ID');
        $this->context->smarty->assign(array(
            'crisp_customer' => $this->context->customer,
            'crisp_website_id' => $website_id
        ));
        return $this->display(__FILE__, 'crisp.tpl');
    }

    public function getContent()
    {
        $get_website_id = Tools::getValue("crisp_website_id");

        if (isset($get_website_id) && !empty($get_website_id)) {
            Configuration::updateValue("WEBSITE_ID", Tools::getValue("crisp_website_id"));
        }
        $website_id =  Configuration::get('WEBSITE_ID');
        
        $is_crisp_working = !empty($website_id);

        $http_callback = "http" . (
            ($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://"
        ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $this->context->smarty->assign(array(
          'website_id' => $website_id,
          "is_crisp_working" => $is_crisp_working,
          "http_callback" => $http_callback
        ));

        $this->context->controller->addJS($this->_path."views/js/base64.js", 'all');
        $this->context->controller->addCSS($this->_path."views/css/style.css", 'all');
        return $this->display(__FILE__, "views/templates/admin/admin.tpl");
    }
}
