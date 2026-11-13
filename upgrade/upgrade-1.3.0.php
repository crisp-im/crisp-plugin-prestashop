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

/**
 * Grant the new webservice permissions to the existing Crisp keys.
 *
 * @param Crisp $module
 *
 * @return bool
 */
function upgrade_module_1_3_0($module)
{
    // Only enable the webservice if the merchant already linked it to Crisp
    $crisp_webservice_keys_count = (int) Db::getInstance()->getValue(
        'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'configuration WHERE name = "CRISP_WEBSERVICE_KEY_ID"'
    );

    if ($crisp_webservice_keys_count > 0) {
        Configuration::updateValue('PS_WEBSERVICE', 1);
    }

    $permissions = [
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

    $crisp_webservice_key_ids = Db::getInstance()->executeS(
        'SELECT DISTINCT value FROM ' . _DB_PREFIX_ . 'configuration WHERE name = "CRISP_WEBSERVICE_KEY_ID"'
    );

    foreach ($crisp_webservice_key_ids ?: [] as $crisp_webservice_key_id) {
        $webserviceKey = new WebserviceKey((int) $crisp_webservice_key_id['value']);

        if (Validate::isLoadedObject($webserviceKey)) {
            WebserviceKey::setPermissionForAccount((int) $webserviceKey->id, $permissions);
        }
    }

    return true;
}
