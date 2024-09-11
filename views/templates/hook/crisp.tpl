{*
*  @author    Crisp IM SAS
*  @copyright 2024 Crisp IM SAS
*  @license   All rights reserved to Crisp IM SAS
*}
<script type='text/javascript'>
  window.CRISP_PLUGIN_URL = "{$crisp_plugin_url|escape:'htmlall':'UTF-8'}";
  window.CRISP_WEBSITE_ID = "{$crisp_website_id|escape:'htmlall':'UTF-8'}";

  if ("{$crisp_chatbox_disabled|escape:'htmlall':'UTF-8'}" !== "1") {
    if ("{$crisp_customer->isLogged()|escape:'htmlall':'UTF-8'}" === "1") {
      CRISP_CUSTOMER = {
        id:  {($crisp_customer->id) ? $crisp_customer->id : 'null'},
        logged_in: true,
        full_name: "{$crisp_customer->firstname|escape:'javascript':'UTF-8'} {$crisp_customer->lastname}",
        email: "{$crisp_customer->email|escape:'javascript':'UTF-8'}",
        address: "{$crisp_customer_address|escape:'javascript':'UTF-8'}",
        phone: "{$crisp_customer_phone|escape:'javascript':'UTF-8'}",
      }
    }

    {if count(json_decode($productsData)) > 0}
      CRISP_CART = {
        id: {($cartId) ? $cartId : 'null'},
        currency_id: {($currencyId) ? $currencyId : 'null'},
        products: JSON.parse('{$productsData}'.replace(/&quot;/g,'"'))
      }
    {/if}
  }
</script>
