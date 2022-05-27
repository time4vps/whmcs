<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

if (!defined("WHMCS")) die("This file cannot be accessed directly");

require 'defines.php';

use Time4VPS\API\Product;
use Time4VPS\API\Script;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use WHMCS\Database\Capsule;

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/includes/helpers.php';
require_once dirname(__FILE__) . '/includes/server.php';
require_once dirname(__FILE__) . '/includes/clientarea.php';
require_once dirname(__FILE__) . '/includes/adminarea.php';

/**
 * Module metadata
 *
 * @return array
 */
function time4vps_MetaData()
{
    return [
        'DisplayName'    => 'Time4VPS Reseller Module',
        'APIVersion'     => '1.1',
        'RequiresServer' => true,
    ];
}

/**
 * Module configuration options
 *
 * @return array
 */
function time4vps_ConfigOptions()
{
    return [
        "product" => [
            "FriendlyName" => "Product",
            "Type" => "dropdown",
            "Loader" => "time4vps_ProductLoaderFunction",
            "SimpleMode" => true
        ],
        "init_script" => [
            "FriendlyName" => "Init Script",
            "Type" => "dropdown",
            "Loader" => "time4vps_InitScriptLoaderFunction",
            "SimpleMode" => true
        ],
        "show_oses" => [
            "FriendlyName" => "OS List",
            "Type" => "textarea",
            "Rows" => "3",
            "Cols" => "50",
            "Description" => "OS list visible to customer (each in new line)",
            "SimpleMode" => true
        ],
        "promo_code" => [
            "FriendlyName" => "Promotion code",
            "Type" => "textarea",
            "SimpleMode" => true,
            "Description" => "Promotion code"
        ],
        "component_map" => [
            "FriendlyName" => "Component Map",
            "Type" => "textarea",
            "SimpleMode" => false,
            "Description" => "JSON formated object (WHMCS component ID = Time4VPS component ID)"
        ]
    ];
}

/**
 * Loads product list in Module configuration menu
 *
 * @param $params
 * @return array
 * @throws APIException
 * @throws AuthException
 */
function time4vps_ProductLoaderFunction($params)
{
    time4vps_InitAPI($params);

    $products = new Product();

    $available_products = [];
    foreach ($products->getAvailableVPS() as $product) {
        $available_products[$product['id']] = $product['name'];
    }

    return $available_products;
}

/**
 * Get available scripts for server provisioning
 *
 * @param $params
 * @return array
 * @throws APIException
 * @throws AuthException
 */
function time4vps_InitScriptLoaderFunction($params)
{
    time4vps_InitAPI($params);

    $script = new Script();

    $available_scripts = ['' => ''];
    foreach ($script->all() as $script) {
        $available_scripts[$script['id']] = "{$script['name']} ({$script['syntax']})";
    }

    return $available_scripts;
}

/**
 * Test API connection
 *
 * @param $params
 * @return array
 */
function time4vps_TestConnection($params)
{
    try {
        time4vps_ProductLoaderFunction($params);
        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        $success = false;
        $errorMsg = $e->getMessage();
    }

    return [
        'success' => $success,
        'error' => $errorMsg
    ];
}

/**
 * Show Client Area
 *
 * @param $params
 * @return array|string
 */
function time4vps_ClientArea($params)
{
    return time4vps_ParseClientAreaRequest($params);
}

/**
 * Custom Admin Area Buttons
 *
 * @return array
 */
function time4vps_AdminCustomButtonArray()
{
    return [
        'Update Details' => 'UpdateServerDetails'
    ];
}
