{**
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
 *}

<script>
  const CRISP_PLUGIN_ID = "{$crisp_plugin_identifier|escape:'javascript':'UTF-8'}";
  const CRISP_PLUGIN_URL = "{$crisp_plugin_url|escape:'javascript':'UTF-8'}";
  const CRISP_PLUGIN_SOURCE = "{$crisp_plugin_source|escape:'javascript':'UTF-8'}";
  const API_KEY = "{$crisp_api_key|escape:'javascript':'UTF-8'}";
  const WEBSITE_ID = "{$website_id|escape:'javascript':'UTF-8'}";
  const IS_CONNECTED = "{$is_crisp_working|escape:'javascript':'UTF-8'}";
  const WEBSERVICE_KEY_ID = "{$crisp_webservice_key_id|escape:'javascript':'UTF-8'}";
  const CALLBACK_URL = "{$http_callback|escape:'javascript':'UTF-8'}";
  const ADMIN_URL = "{$admin_url|escape:'javascript':'UTF-8'}";
  const ADMIN_LOCALE = "{$admin_locale|escape:'javascript':'UTF-8'}";
  const CHATBOX_DISABLED = "{$chatbox_disabled|escape:'javascript':'UTF-8'}";
  const API_KEY_DISABLED = "{$api_key_disabled|escape:'javascript':'UTF-8'}";
  const API_KEY_INVALID = "{$api_key_invalid|escape:'javascript':'UTF-8'}";
  const SHOP_NAME = "{$shop_name|escape:'javascript':'UTF-8'}";
  const SHOP_DOMAIN = "{$shop_domain|escape:'javascript':'UTF-8'}";
  const USER_EMAIL = "{$user_email|escape:'javascript':'UTF-8'}";
  const USER_NAME = "{$user_name|escape:'javascript':'UTF-8'}";
</script>

<div id="app" v-scope="InitPreferences(API_KEY, WEBSITE_ID, IS_CONNECTED, WEBSERVICE_KEY_ID, CALLBACK_URL, ADMIN_URL, ADMIN_LOCALE, CHATBOX_DISABLED, API_KEY_DISABLED)" @vue:mounted="mounted" class="container">
  <div>
    <h1 class="mb-3">
      <img id="logo" src="{$logo|escape:'javascript':'UTF-8'}">
      {l s='Welcome to your Crisp Integration' mod='crisp'}
    </h1>
  </div>

  <div>
    <div class="row mb-2">
      <div class="col-md-5 col-12 pr-1 pl-0">
        <prestashop-accounts style="height:100%;display:flex;width:100%"></prestashop-accounts>
      </div>
      <div class="col-md-7 col-12 pl-1 pr-0">
        <div id="prestashop-cloudsync" style="height:100%;display: flex;"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 p-0">
        <div v-if="!store.psAccountsConnected" class="alert alert-warning" role="alert">
          <p class="alert-text">
            {l s='Please associate your Prestashop account with this module before continuing.' mod='crisp'}
          </p>
        </div>

        <div id="crisp">
          <div v-if="store.isConnected" class="crisp-panel">
            <div class="wrap crisp-wrap">
              <div class="crisp-modal">
                <div class="crisp-title">{l s='Configure Crisp for your Prestashop Store' mod='crisp'}</div>
                {if $crisp_installed == true}
                  <p class="alert alert-success">{l s='Crisp has been successfully linked with your Prestashop data.' mod='crisp'}</p>
                {/if}
                <div id="installcrisp" class="d-flex flex-column">
                  <a class="crisp-button crisp u-mb20" href="https://app.crisp.chat/website/{$website_id|escape:'htmlall':'UTF-8'}/inbox" target="_blank">💬 {l s='Open Crisp inbox' mod='crisp'}</a>
                  <a class="crisp-button crisp-neutral u-mb20" href="https://app.crisp.chat/settings/website/{$website_id|escape:'htmlall':'UTF-8'}" target="_blank">⚙️ {l s='Go to my Crisp settings' mod='crisp'}</a>
                  <a class="crisp-button crisp-neutral u-mb20" onclick="actions.linkWithCrispCloudSync()">🪄 {l s='Relink Crisp to my Prestashop' mod='crisp'}</a>

                  <div v-if="store.webservice.key !== ''">
                    <div class="crisp-title">{l s='Enable your Stores Webservice' mod='crisp'}</div>

                    <div v-if="store.webservice.loading" class="crisp-loading">
                      <div class="spinner"></div>
                      <div>
                        <span class="crisp-label-subdued pt-4"> Connecting webservice to Crisp... </span>
                      </div>
                    </div>

                    <div v-else>
                      <div v-if="store.webservice.success !== ''" class="alert alert-success" role="alert">
                        <p class="alert-text">
                          {l s='[[store.webservice.success]]' mod='crisp'}
                        </p>
                      </div>

                      <div v-if="store.webservice.error !== ''" class="alert alert-danger" role="alert">
                        <p class="alert-text">
                          {l s='[[store.webservice.error]]' mod='crisp'}
                        </p>
                      </div>

                      <div v-if="store.webservice.success === ''">
                        <div v-if="store.webservice.invalid">
                          <p v-if="store.webservice.error === ''" class="alert alert-warning">{l s='You have a Crisp webservice key but it is not enabled. Please link the webservice and enabled it in Advanced Parameters > Webservice' mod='crisp'}</p>

                          <a v-if="!store.webservice.enabled" id="installcrisp" class="crisp-button crisp" onclick="actions.linkWithCrispWebservice()">{l s='Link Webservice to Crisp' mod='crisp'}</a>
                          <a v-else class="crisp-button crisp-neutral u-mb20" onclick="actions.linkWithCrispWebservice()">🪄 {l s='Relink Webservice to Crisp' mod='crisp'}</a>
                        </div>

                        <div v-else-if="store.webservice.error === ''" class="alert alert-warning" role="alert">
                          <p class="alert-text">
                            {l s='Prestashop data is now syncing with Crisp through Cloudsync. Enable your stores webservice in order to view customers\' data from Crisp.' mod='crisp'}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="crisp-options">
                    <div class="crisp-title">{l s='Additional Options' mod='crisp'}</div>

                    <div class="crisp-option">
                      <label for="crisp-chatbox">
                        {l s='Include Crisp Chatbox on storefront.' mod='crisp'} <span class="crisp-label-subdued"> {l s='Enable this option to show the Chatbox on you store.' mod='crisp'}</span>
                      </label>
                      <input v-model="store.chatboxEnabled" onchange="actions.toggleChatbox()" data-toggle="switch" class="switch-input-lg" id="crisp-chatbox" data-inverse="true" type="checkbox" name="switch[]" ref="chatboxToggle" />
                    </div>

                    <div class="crisp-option">
                      <label for="crisp-chatbox">
                        {l s='Use stores webservice.' mod='crisp'} <span class="crisp-label-subdued"> {l s='Enable this option to allow Crisp to use the stores webservice in order to find customers and their order details.' mod='crisp'}</span>
                      </label>
                      <input v-model="store.webservice.active" onchange="actions.toggleWebservice()" data-toggle="switch" class="switch-input-lg" id="crisp-chatbox" data-inverse="true" type="checkbox" name="switch[]" ref="webserviceToggle" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!store.isConnected" class="crisp-panel">
            <div class="wrap crisp-wrap">
              <div class="crisp-modal">
                <div class="crisp-title">{l s='Link Crisp with your Prestashop data' mod='crisp'}</div>
                <div class="crisp-actions">
                  <p class="crisp-subtitle" class="mb-3">{l s='By clicking this link you will allow data to flow between Crisp and Prestashop' mod='crisp'}</p>
                  <a id="installcrisp" class="crisp-button crisp" href="#" onclick="actions.linkWithCrispCloudSync()">{l s='Install Crisp on my Prestashop' mod='crisp'}</a>
                </div>

                <div class="crisp-side">
                  <div class="crisp-side-illustration"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
<script src="{$urlCloudsync|escape:'htmlall':'UTF-8'}"></script>

<script>
  window?.psaccountsVue?.init();
  store.psAccountsConnected = window?.psaccountsVue?.isOnboardingCompleted();

  if (window.psaccountsVue.isOnboardingCompleted() != true)
  {
    document.getElementById("crisp").style.opacity = "0.5";
    document.getElementById("installcrisp").style.display = "none!important";
  } else {
    store.psAccountsConnected = true;
  }

  // Cloud Sync
  const cdc = window.cloudSyncSharingConsent;
  cdc.init('#prestashop-cloudsync');
</script>
