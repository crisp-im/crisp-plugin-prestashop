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
window.$crisp=[];

(function(){
  d=document;
  s=d.createElement('script');
  s.src='https://client.crisp.chat/l.js';
  s.async=1;
  d.getElementsByTagName('head')[0].appendChild(s);
})();

function handleCrispCartUpdatedEvent(event) {
  if (event?.reason?.cart?.products) {
    let _products = [];
    let _cartProducts = event.resp.cart.products;

    _cartProducts.forEach((product) => {
      const _priceWithoutCurrency =
        product?.price?.replace(/[^\d,.]/g, "").replace(",", ".") || 0;

      _products.push({
        id_product: parseInt(product.id_product),
        id_product_attribute: parseInt(product.id_product_attribute),
        quantity: parseInt(product.quantity),
        price: parseFloat(_priceWithoutCurrency),
      });
    });

    if (_products.length > 0) {
      var _cart = {
        currency_code: prestashop?.currency?.iso_code,
        currency_id: prestashop?.currency?.id,
        products: _products,
      };

      postCrispCartData(_cart);
    }
  }
}

function postCrispCartData(cart) {
  var identifier = $crisp.get("session:identifier");
  var website_id = window.CRISP_WEBSITE_ID;

  fetch(
    window.CRISP_PLUGIN_URL +
      "/visitors/website/" +
      website_id +
      "/session/" +
      identifier +
      "/cart",
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(cart),
    }
  );
}

window.CRISP_READY_TRIGGER = async function() {
  // Set session segment (only after first message is sent)
  $crisp.push(["on", "message:sent", () => {
    $crisp.push(["set", "session:segments", [["prestashop", "chat"]]]);
    $crisp.push(["off", "message:sent"]);
  }])

  // Listen for cart updated events
  prestashop.on("updateCart", function (event) {
    handleCrispCartUpdatedEvent(event)
  })

  if (window?.CRISP_CUSTOMER?.logged_in) {
    var identifier = $crisp.get('session:identifier');
    var website_id = window.CRISP_WEBSITE_ID;

    $crisp.push(["set", "user:nickname", CRISP_CUSTOMER.full_name]);
    $crisp.push(["set", "user:email", CRISP_CUSTOMER.email]);
    $crisp.push(["set", "user:phone", CRISP_CUSTOMER.phone]);
    $crisp.push(["set", "session:data", [[["prestashop_customer_id", CRISP_CUSTOMER.id], ["prestashop_address", CRISP_CUSTOMER.address]]]])

    fetch(window.CRISP_PLUGIN_URL+"/visitors/website/"+website_id+"/session/"+identifier+"/customer", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        "customer_id": CRISP_CUSTOMER.id,
        "customer_email": CRISP_CUSTOMER.email
      }),
    });
  }

  if (window?.CRISP_CART && CRISP_CART?.products.length > 0) {
    var _cart = {
      "currency_code": prestashop?.currency?.iso_code,
      "cart_id": CRISP_CART.id,
      "currency_id": CRISP_CART.currency_id,
      "products" : CRISP_CART.products,
    };
  
    postCrispCartData(_cart);
  }
};
