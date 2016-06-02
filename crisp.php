<?php

/** Crisp Communications **/

if (!defined('_PS_VERSION_'))
  exit;

class crisp extends Module{

  public function __construct()
  {
    $this->name = 'crisp';
    $this->tab = 'front_office_features';
    $this->version = 0.1;

    parent::__construct();

    $this->page = basename(__FILE__, '.php');
    $this->displayName = $this->l('Crisp');
    $this->description = $this->l('Beatufil Live chat and live support for your product');
    $this->module_key = 'cc67e1a6e3a327f43ecc8037cd7f459e';
  }

  public function install()
  {
    if (parent::install() == False)
      return False;
    return $this->registerHook('displayHeader');
  }

  public function uninstall()
  {
    if(parent::uninstall() == False)
      return False;

    return true;
  }

  public function hookDisplayHeader($params)
  {
    $website_id = Configuration::get('WEBSITE_ID');
    print "<script type='text/javascript'>CRISP_WEBSITE_ID = '$website_id';(function(){d=document;s=d.createElement('script');s.src='https://client.crisp.im/l.js';s.async=1;d.getElementsByTagName('head')[0].appendChild(s);})();</script>";
    return true;
  }

  public function getContent()
  {
      $output = null;

      if (Tools::isSubmit('submit'.$this->name))
      {
          $website_id = strval(Tools::getValue('WEBSITE_ID'));
          if (!$website_id
            || empty($website_id))
              $output .= $this->displayError($this->l('Invalid Website ID value'));
          else
          {
              Configuration::updateValue('WEBSITE_ID', $website_id);
              $output .= $this->displayConfirmation($this->l('Settings updated'));
          }
      }
      return $output.$this->displayForm();
  }

  public function displayForm()
{
    // Get default language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Settings'),
            'image' => $this->_path.'/logo-32.png'
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('Website ID value'),
                'name' => 'WEBSITE_ID',
                'size' => 20,
                'required' => true
            )
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'button'
        )
    );

    $helper = new HelperForm();

    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;

    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;
    $helper->toolbar_scroll = true;
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );

    // Load current value
    $helper->fields_value['WEBSITE_ID'] = Configuration::get('WEBSITE_ID');

    $form = $helper->generateForm($fields_form);

    $form .= "<a href='https://app.crisp.im/#/settings/websites'>Get your Website ID here</a>";
    return $form;
}

}

?>
