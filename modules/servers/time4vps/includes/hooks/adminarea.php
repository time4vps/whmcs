<?php /** @noinspection ALL */

use WHMCS\Database\Capsule;

/** Set service as pending after service creation */
add_hook('AfterModuleCreate', 1, function ($params) {
    Capsule::table('tblhosting')->where('id', $params['serviceid'])->update(['domainstatus' => 'Pending']);
});
