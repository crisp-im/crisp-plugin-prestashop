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

    return $module->ensureWebservicePermissions();
}
