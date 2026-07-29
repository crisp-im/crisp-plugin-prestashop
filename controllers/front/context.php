<?php
/**
 * Crisp Module
 *
 * @author    Crisp IM SAS
 * @copyright 2026 Crisp IM SAS
 * @license   All rights reserved to Crisp IM SAS
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CrispContextModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $ajax = true;

    /** @var bool */
    public $auth = false;

    public function displayAjax()
    {
        $this->sendNoStoreHeaders();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $this->ajaxRender(json_encode(['error' => 'Method not allowed']));

            return;
        }

        if (!Configuration::get('WEBSITE_ID') || Configuration::get('CRISP_CHATBOX_DISABLED')) {
            http_response_code(404);
            $this->ajaxRender(json_encode(['error' => 'Crisp is not available']));

            return;
        }

        $this->ajaxRender(json_encode([
            'customer' => $this->getCustomerData(),
            'cart' => $this->getCartData(),
        ]));
    }

    private function sendNoStoreHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Vary: Cookie');
        header('X-Content-Type-Options: nosniff');
    }

    private function getCustomerData()
    {
        $customer = $this->context->customer;

        if (!$customer || !$customer->isLogged()) {
            return null;
        }

        $customerAddress = '';
        $customerPhone = '';
        $addresses = $customer->getAddresses($this->context->language->id);

        if (!empty($addresses)) {
            $address = $addresses[0];
            $street = trim(($address['address1'] ?? '') . ' ' . ($address['address2'] ?? ''));
            $city = trim(($address['postcode'] ?? '') . ' ' . ($address['city'] ?? ''));
            $customerAddress = implode(', ', array_filter([
                $street,
                $city,
                $address['country'] ?? '',
            ]));
            $customerPhone = $address['phone'] ?? '';
        }

        return [
            'id' => (int) $customer->id,
            'logged_in' => true,
            'full_name' => trim($customer->firstname . ' ' . $customer->lastname),
            'email' => $customer->email,
            'address' => $customerAddress,
            'phone' => $customerPhone,
        ];
    }

    private function getCartData()
    {
        $cart = $this->context->cart;

        if (!$cart || !$cart->id) {
            return null;
        }

        $products = [];

        foreach ($cart->getProducts() as $product) {
            $products[] = [
                'id_product' => (int) $product['id_product'],
                'id_product_attribute' => (int) $product['id_product_attribute'],
                'quantity' => (int) $product['quantity'],
                'price' => (float) $product['price'],
            ];
        }

        if (empty($products)) {
            return null;
        }

        return [
            'id' => (int) $cart->id,
            'currency_id' => (int) $cart->id_currency,
            'products' => $products,
        ];
    }
}
