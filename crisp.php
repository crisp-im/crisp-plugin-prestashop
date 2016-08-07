<?php

/** Crisp IM **/

if (!defined('_PS_VERSION_'))
  exit;

class crisp extends Module {

  public function __construct()
  {
    $this->name = 'crisp';
    $this->displayName = $this->l('Crisp Livechat');
    $this->author = 'Crisp IM';
    $this->tab = 'front_office_features';
    $this->version = 0.3;

    parent::__construct();

    $this->page = basename(__FILE__, '.php');
    $this->displayName = $this->l('Crisp');
    $this->description = $this->l('Crisp is a free and beautiful livechat to interact with customers.');
  }

  public function install()
  {
    return (parent::install() && $this->registerHook('displayHeader') && $this->installTab());
  }

  public function uninstall()
  {
    return(parent::uninstall() == False && $this->uninstallTab());
  }

  public function installTab() {
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

  public function uninstallTab() {
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
      'website_id' => $website_id
    ));
    return $this->display(__FILE__, 'crisp.tpl');
  }


  public function getContent()
  {
    if (isset($_GET["crisp_website_id"]) && !empty($_GET["crisp_website_id"])) {
      Configuration::updateValue("WEBSITE_ID", $_GET["crisp_website_id"]);
    }
    $website_id =  Configuration::get('WEBSITE_ID');
    $is_crisp_working = isset($website_id) && !empty($website_id);
    $http_callback = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $payload = base64_encode($http_callback);

    $add_to_crisp_link = "https://app.crisp.im/initiate/plugin/be40c894-22bb-408c-8fdc-aafb5e6b1985?payload=$payload";

    $this->context->smarty->assign(array(
      'img_check' => $this->_path. "views/images/check.png",
      'img_link_with_crisp' => $this->_path. "views/images/link-with-crisp.png",
      'add_to_crisp_link' => $add_to_crisp_link,
      "is_crisp_working" => $is_crisp_working
    ));


    $this->context->controller->addCSS($this->_path."views/styles/style.css", 'all');
    return $this->display(__FILE__, "views/templates/admin/admin.tpl");
  }
}

?>
