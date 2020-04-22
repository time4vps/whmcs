<?php
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedFunctionInspection */

use Time4VPS\API\Server;
use Time4VPS\Base\Endpoint;
use WHMCS\Database\Capsule;

/**
 * Time4VPS API Initialisation function
 *
 * @param $params
 */
function time4vps_InitAPI($params)
{
    $debug = new Time4VPS\Base\Debug();
    Endpoint::BaseURL("{$params['serverhttpprefix']}://{$params['serverhostname']}/api/");
    Endpoint::Auth($params['serverusername'], $params['serverpassword']);
    Endpoint::DebugFunction(function ($args, $request, $response) use ($debug) {
        $id = hash('crc32', microtime(true));
        $benchmark = $debug->benchmark();
        logModuleCall('Time4VPS', "(id: {$id}) '{$args[0]}' request to '{$args[1]}'", json_encode($request, JSON_PRETTY_PRINT), (string) $response);
        localAPI('LogActivity', [
            'description' => "Time4VPS API request (id: {$id}) took {$benchmark} s."
        ]);
    });
}

/**
 * Get Time4VPS server ID from params
 *
 * @param $params
 * @return Server|false External server ID or false
 * @throws \Time4VPS\Exceptions\Exception
 */
function time4vps_ExtractServer($params)
{
    if ($server = Capsule::table(TIME4VPS_TABLE)->where('service_id', $params['serviceid'])->first()) {
        /** @var Server $s */
        return new Server($server->external_id);
    }

    return false;
}

function time4vps_GetComponentIdByName($name, $pid)
{
    $component = Capsule::table('tblproductconfigoptions')
        ->select('tblproductconfigoptions.id')
        ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
        ->where('tblproductconfigoptions.name', $name)
        ->where('tblproductconfiglinks.pid', $pid)
        ->first();

    return $component ? $component->id : null;
}

/**
 * Return main page link
 *
 * @param $params
 * @return string
 */
function time4vps_ProductDetailsLink($params)
{
    return "clientarea.php?action=productdetails&id={$params['serviceid']}";
}

/**
 * Return action link
 *
 * @param $params
 * @param $action
 * @return string
 */
function time4vps_ActionLink($params, $action)
{
    return "clientarea.php?action=productdetails&id={$params['serviceid']}&act={$action}";
}

/**
 * Redirect user to URL
 *
 * @param $url
 */
function time4vps_Redirect($url)
{
    header("Location: {$url}");
    exit();
}
