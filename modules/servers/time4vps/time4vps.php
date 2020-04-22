<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

if (!defined("WHMCS")) die("This file cannot be accessed directly");

define('TIME4VPS_TABLE', 'mod_time4vps');

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Time4VPS\API\Product;
use Time4VPS\API\Script;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use WHMCS\Database\Capsule;

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
 * Module activation function
 */
function time4vps_Activate()
{
    /** Disable "Configure Server" on order page */
    $tpl_path = ROOTDIR . "/templates/orderforms/standard_cart/configureproduct.tpl";
    $tpl = file_get_contents($tpl_path);

    // Make a copy
    $tpl_path_copy = $tpl_path . '.orig.tpl';
    if (!file_exists($tpl_path_copy)) {
        file_put_contents($tpl_path_copy, $tpl);
    }

    // Change fields
    $repl = '$1 && $productinfo.module eq "time4vps"}' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="hostname" value="servername.yourdomain.com" />' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="rootpw" value="rootpwd" />' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="ns1prefix" value="ns1" />' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="ns2prefix" value="ns2" />' . PHP_EOL;
    $repl .= "\t\t\t" . '{else $1 && $productinfo.module neq "time4vps"}' . PHP_EOL;

    $tpl = preg_replace('/(if \$productinfo\.type eq "server")}/', $repl, $tpl);
    file_put_contents($tpl_path, $tpl);

    /** Backwards compatability */
    if (Capsule::schema()->hasTable('sm_time4vps')) {
        Capsule::schema()
            ->table('sm_time4vps', function ($table) {
                /** @var $table Object */
                $table->text('details')->nullable();
                $table->timestamp('details_updated')->nullable();
            })
            ->rename('sm_time4vps', TIME4VPS_TABLE);
    }

    /** Create service ID maping table */
    if (!Capsule::schema()->hasTable(TIME4VPS_TABLE)) {
        Capsule::schema()->create(
            TIME4VPS_TABLE,
            function ($table) {
                /** @var $table Object */
                $table->integer('service_id')->unique();
                $table->integer('external_id')->index();
                $table->text('details')->nullable();
                $table->timestamp('details_updated')->nullable();
            }
        );
    }
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
