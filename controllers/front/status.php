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
 * Exposes the installed module version, so that Crisp can tell merchants when to update it
 */
class CrispStatusModuleFrontController extends ModuleFrontController
{
    public const EDITION = 'prestashop-8';

    /** @var bool */
    public $ajax = true;

    /** @var bool */
    public $auth = false;

    public function displayAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            $this->ajaxRender(json_encode(['error' => 'Method not allowed']));

            return;
        }

        $this->ajaxRender(json_encode([
            'module' => 'crisp',
            'version' => $this->module->version,
            'edition' => self::EDITION,
        ]));
    }
}
