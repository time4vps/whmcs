<?php /** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use WHMCS\Database\Capsule;

function time4vps_UpdateServerDetails($params)
{
    try {
        time4vps_GetServerDetails($params, true);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    return 'success';
}