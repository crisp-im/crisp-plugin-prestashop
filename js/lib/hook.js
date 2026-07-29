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

async function fetchCrispContext() {
  if (!window.CRISP_CONTEXT_URL) {
    return null;
  }

  var response = await fetch(window.CRISP_CONTEXT_URL, {
    method: "POST",
    credentials: "same-origin",
    cache: "no-store",
    headers: {
      "Accept": "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  });

  if (!response.ok) {
    return null;
  }

  return response.json();
}

var crisp_context_promise = fetchCrispContext().catch(function() {
  return null;
});

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

  var crisp_context = await crisp_context_promise;
  var crisp_customer = crisp_context?.customer;

  if (crisp_customer?.logged_in) {
    var identifier = $crisp.get('session:identifier');
    var website_id = window.CRISP_WEBSITE_ID;

    $crisp.push(["set", "user:nickname", crisp_customer.full_name]);
    $crisp.push(["set", "user:email", crisp_customer.email]);
    $crisp.push(["set", "user:phone", crisp_customer.phone]);
    $crisp.push(["set", "session:data", [[["prestashop_customer_id", crisp_customer.id], ["prestashop_address", crisp_customer.address]]]])

    fetch(window.CRISP_PLUGIN_URL+"/visitors/website/"+website_id+"/session/"+identifier+"/customer", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        "customer_id": crisp_customer.id,
        "customer_email": crisp_customer.email
      }),
    });
  }

  var crisp_cart = crisp_context?.cart;

  if (crisp_cart?.products.length > 0) {
    var _cart = {
      "currency_code": prestashop?.currency?.iso_code,
      "cart_id": crisp_cart.id,
      "currency_id": crisp_cart.currency_id,
      "products" : crisp_cart.products,
    };
  
    postCrispCartData(_cart);
  }
};
