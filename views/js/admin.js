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

const CRISP_PLUGIN_DEFAULT_ID = "be40c894-22bb-408c-8fdc-aafb5e6b1985"
const CRISP_INSTALL_LINK = "https://app.crisp.chat/initiate/plugin/"
const AJAX_WEBSERVICE = "&action=enableWebService&ajax=true";
const AJAX_WEBSERVICE_ENABLE = "&action=toggleEnableWebservice&ajax=true";
const AJAX_WEBSERVICE_DISABLE = "&action=toggleDisableWebservice&ajax=true";
const AJAX_CHATBOX_ENABLE = "&action=enableChatbox&ajax=true";
const AJAX_CHATBOX_DISABLE = "&action=disableChatbox&ajax=true";

/********************************************************************
 * Translations
 *********************************************************************/
const ERROR_MESSAGES = {
  WEBSERVICE_GENERIC : {
    default: "An error occurred when connecting your webservice to Crisp. Please try again.",
    fr: "Une erreur s'est produite lors de la connexion de votre boutique à Crisp. Veuillez réessayer.",
    es: "Se ha producido un error al conectar su webservice a Crisp. Inténtelo de nuevo."
  },

  WEBSERVICE_PLUGIN_URL: {
    default: "An error occurred when connecting your webservice to Crisp. The Crisp plugin URL is missing. Please try again.",
    fr: "Une erreur s'est produite lors de la connexion de votre boutique à Crisp. L'URL du plugin Crisp est manquante. Veuillez réessayer.",
    es: "Se ha producido un error al conectar su servicio web a Crisp. Falta la URL del plugin de Crisp. Vuelva a intentarlo."
  },

  WEBSERVICE_MISSING_PARAMS: {
    default: "An error occurred when connecting your webservice to Crisp due to missing parameters. Please try again.",
    fr: "Une erreur s'est produite lors de la connexion de votre boutique à Crisp en raison de paramètres manquants. Veuillez réessayer.",
    es: "Se ha producido un error al conectar su servicio web a Crisp debido a la falta de parámetros. Vuelva a intentarlo."
  },

  WEBSERVICE_UNAUTHORIZED: {
    default: "An error occurred. The authenticity of the request cannot be validated, please reconnect the module by clicking 'Relink Crisp to Prestashop' and try again.",
    fr: "Une erreur s'est produite. L'authenticité de la demande ne peut être validée, veuillez reconnecter le module en cliquant sur 'Re-link Crisp to Prestashop' et réessayer.",
    es: "Se ha producido un error. No se ha podido validar la autenticidad de la solicitud. Vuelva a conectar el módulo haciendo clic en 'Volver a vincular Crisp a Prestashop' e inténtelo de nuevo."
  }
}

const SUCCESS_MESSAGES = {
  WEBSERVICE_GENERIC : {
    default: "Your stores Webservice is now connected to Crisp.",
    fr: "Les données de votre boutique sont maintenant connectées à Crisp.",
    es: "El servicio web de su tienda está ahora conectado a Crisp."
  },
}

const store = PetiteVue.reactive({
  psAccountsConnected: false,
  isConnected: false,
  chatboxEnabled: true,
  adminUrl: "",
  callbackUrl: "",
  websiteId: "",
  crispInstallLink: CRISP_INSTALL_LINK,
  adminLocale: "default",

  webservice: {
    loading: false,
    enabled: false,
    active: false,
    invalid: false,
    key: "",
    success: "",
    error: ""
  },

  $ref: Proxy
});

const actions = {
  /**
   * @public
   * @return {undefined}
   */
  linkWithCrispCloudSync() {
    if (window.psaccountsVue.isOnboardingCompleted() != true) {
      return;
    }

    var _redirectURL = btoa(store.callbackUrl);
    var _shopID = contextPsAccounts.currentShop.uuid;
    var _payload = "redirect=" + _redirectURL + "&shop_id=" + _shopID + "&plugin_source=" + CRISP_PLUGIN_SOURCE;
    var add_to_crisp_link = store.crispInstallLink + btoa(_payload);

    if (SHOP_NAME !== "") {
      add_to_crisp_link += "&website_name=" + SHOP_NAME;
    }

    if (SHOP_DOMAIN !== "") {
      add_to_crisp_link += "&website_domain=" + SHOP_DOMAIN;
    } else {
      add_to_crisp_link += "&website_domain=" + window.location.host;
    }

    if (USER_EMAIL !== "") {
      add_to_crisp_link += "&user_email=" + USER_EMAIL;
    }

    if (USER_NAME !== "") {
      add_to_crisp_link += "&user_name=" + USER_NAME;
    }

    window.open(add_to_crisp_link, "_self");
  },

  /**
   * @public
   * @return {undefined}
   */
  linkWithCrispWebservice() {
    store.webservice.loading = true;

    // Enabled webservice and create new key
    $.ajax({
      url: store.adminUrl + AJAX_WEBSERVICE + "&crisp_api_key=" + store.webservice.key,
      success: function () {
        // Enabled webservice in Crisp.
        actions.authenticateWebserviceInCrisp();
      },

      error: function () {
        store.webservice.loading = false;

        actions.__setWebserviceError(ERROR_MESSAGES.WEBSERVICE_GENERIC);
      },
    });
  },

  /**
   * @public
   * @return {undefined}
   */
  authenticateWebserviceInCrisp() {
    let _params = new URLSearchParams(store.callbackUrl)
    let _token = _params.get("token")

    if (CRISP_PLUGIN_URL === "") {
      actions.__setWebserviceError(ERROR_MESSAGES.WEBSERVICE_PLUGIN_URL);
      actions.__invalidateWebserviceKeys();

      return;
    }

    if (_token === "" || store.websiteId === "") {
      actions.__setWebserviceError(ERROR_MESSAGES.WEBSERVICE_MISSING_PARAMS);

      return;
    }

    // Form URL to enable webservice client in Crisp
    const _url = `${CRISP_PLUGIN_URL}/admin/config/webservice/auth?website_id=${store.websiteId}&token=${_token}`

    fetch(_url).then(((response) => {
      store.webservice.loading = false;
      store.webservice.active = true;

      if (response.status >= 200 && response.status < 299) {
        actions.__setWebserviceSuccess(SUCCESS_MESSAGES.WEBSERVICE_GENERIC);
        actions.__updateWebserviceToggle();
      } else if (response.status === 401) {
        actions.__setWebserviceError(ERROR_MESSAGES.WEBSERVICE_UNAUTHORIZED);
      } else {
        actions.__setWebserviceError(ERROR_MESSAGES.WEBSERVICE_GENERIC);
      }

    })).catch(() => {
      store.webservice.loading = false;

      actions.__setWebserviceError(ERROR_MESSAGES.WEBSERVICE_GENERIC);
      actions.__invalidateWebserviceKeys();
    })
  },

  /**
   * @public
   * @return {undefined}
   */
  toggleChatbox() {
    var _url = store.adminUrl;

    if (store.chatboxEnabled) {
      _url += AJAX_CHATBOX_DISABLE;
    } else {
      _url += AJAX_CHATBOX_ENABLE;
    }

    $.ajax({
      url: _url,
      success: () => {
        store.chatboxEnabled = !store.chatboxEnabled;
      },

      error: () => {
        actions.__updateChatboxToggle();
      }
    }).then(() => {
      actions.__updateChatboxToggle();
    });
  },

  /**
   * @public
   * @return {undefined}
   */
  toggleWebservice() {
    var _url = store.adminUrl;

    if (store.webservice.active) {
      _url += AJAX_WEBSERVICE_DISABLE;
    } else {
      _url += AJAX_WEBSERVICE_ENABLE;
    }

    $.ajax({
      url: _url,
      success: () => {
        store.webservice.active = !store.webservice.active;
      },

      error: () => {
        actions.__updateWebserviceToggle();
      }
    }).then(() => {
      actions.__updateWebserviceToggle();
    });
  },

  /**
   * @private
   * @return {undefined}
   */
  __updateChatboxToggle() {
    var _chatboxClassList = store.$refs?.chatboxToggle?.parentElement?.classList;

    if (store.chatboxEnabled) {
      _chatboxClassList.add("-checked")
    } else {
      _chatboxClassList.remove("-checked")
    }
  },

  /**
   * @private
   * @return {undefined}
   */
  __updateWebserviceToggle() {
    var _webserviceClassList = store.$refs?.webserviceToggle?.parentElement?.classList;

    _webserviceClassList.remove("switch-input-disabled")

    if (store.webservice.active) {
      _webserviceClassList.add("-checked")

      actions.__setWebserviceSuccess(SUCCESS_MESSAGES.WEBSERVICE_GENERIC);
    } else {
      _webserviceClassList.remove("-checked")
    }
  },

  /**
   * @private
   * @param {object} messages
   * @return {undefined}
   */
  __setWebserviceError(messages) {
    // Set default message
    let _message = messages.default;

    // Reset success message
    store.webservice.success = "";

    // Use store language
    if (messages[store.adminLocale]) {
      _message = messages[store.adminLocale]
    }

    // Set error message
    store.webservice.error = _message;

    // Message set stop loading
    store.webservice.loading = false;
  },

  /**
   * @private
   * @param {object} messages
   * @return {undefined}
   */
  __setWebserviceSuccess(messages) {
    console.log(messages)
    console.log(messages.default)
    console.log(store.adminLocale)
    console.log(messages[store.adminLocale])

    // Set default message
    let _message = messages.default;

    // Reset error message
    store.webservice.error = "";

    // Use store language
    if (messages[store.adminLocale]) {
      _message = messages[store.adminLocale]
    }

    // Set success message
    store.webservice.success = _message;

    // Message set stop loading
    store.webservice.loading = false;
  },

  /**
    * @private
    * @returns {undefined}
    */
  __invalidateWebserviceKeys() {
    // Enabled webservice and create new key
    $.ajax({
      url: store.adminUrl + AJAX_WEBSERVICE + "&crisp_api_key=401-" + store.webservice.key,
      success: function () {
        console.log("Webservice keys invalidated.")
      },

      error: function () {
        console.error("Could not invalidate webservice keys.")
      },
    });
  },
};

/**
 * Loads preferences into store
 * @returns {object}  empty object
 */
function InitPreferences() {
  store.websiteId = WEBSITE_ID;
  store.callbackUrl = CALLBACK_URL;
  store.adminUrl = ADMIN_URL;
  store.adminLocale = ADMIN_LOCALE;
  store.webservice.key = API_KEY;

  if (CRISP_PLUGIN_ID !== "") {
    store.crispInstallLink += CRISP_PLUGIN_ID + "?payload=";
  } else {
    store.crispInstallLink += CRISP_PLUGIN_DEFAULT_ID + "?payload=";
  }

  if (IS_CONNECTED === "1") {
    store.isConnected = true;
  }

  if (CHATBOX_DISABLED === "1") {
    store.chatboxEnabled = false;
  }

  if (API_KEY_DISABLED === "1") {
    store.webservice.active = false;
  } else {
    store.webservice.active = true;
  }

  if (API_KEY_INVALID === "1") {
    store.webservice.invalid = true;
    store.webservice.active = false;

    setTimeout(() => {
      var _webserviceClassList = store.$refs?.webserviceToggle?.parentElement?.classList;

      if (_webserviceClassList) {
        _webserviceClassList.add("switch-input-disabled")
      }
    }, 500);
  }

  if (WEBSERVICE_KEY_ID !== "") {
    store.webservice.enabled = true;

    if (store.webservice.active) {
      actions.__setWebserviceSuccess(SUCCESS_MESSAGES.WEBSERVICE_GENERIC);
    }
  }

  return {
    mounted() {
      store.$refs = this.$refs;
    }
  };
}

document.addEventListener("DOMContentLoaded", () => {
  PetiteVue.createApp({
    store,
    actions,
    $delimiters: ["[[", "]]"],
  }).mount("#app");
});
