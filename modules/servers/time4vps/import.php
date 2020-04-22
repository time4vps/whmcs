<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

define('ROOTPATH', dirname(dirname(dirname(dirname(__FILE__) . '../') . '../') . '../'));

require ROOTPATH . '/init.php';

use WHMCS\Database\Capsule;

if (!$_SESSION['adminid']) {
    die('Access denied');
}

if ($_GET['truncate']) {

    // Delete components
    Capsule::table('tblproductconfiglinks')->truncate();
    Capsule::table('tblproductconfiggroups')->truncate();
    Capsule::table('tblproductconfigoptions')->truncate();
    Capsule::table('tblproductconfigoptionssub')->truncate();

    // Delete products
    Capsule::table('tblproducts')->truncate();

    // Delete pricing
    Capsule::table('tblpricing')->truncate();

    // Upgrades
    Capsule::table('tblupgrades')->truncate();

    // Config options
    Capsule::table('tblhostingconfigoptions')->truncate();
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/includes/helpers.php';

$data = Capsule::table('tblservers')->where('type', 'time4vps')->first();

if (!$data) {
    die('No module server found');
}

$decrypt = localAPI('DecryptPassword', ['password2' => $data->password]);

$api = [
    'serverhttpprefix' => $data->secure === 'on' ? 'https' : 'http',
    'serverhostname' => $data->hostname,
    'serverusername' => $data->username,
    'serverpassword' => $decrypt['password']
];

time4vps_InitAPI($api);

$products = (new Time4VPS\API\Product())->getAvailableVPS();

/** Create product group */
$gid = Capsule::table('tblproductgroups')->where(['name' => 'VPS Servers'])->first();
if (!$gid) {
    $gid = Capsule::table('tblproductgroups')->insertGetId(['name' => 'VPS Servers']);
} else {
    $gid = $gid->id;
}

/** Create products */
foreach ($products as $product) {

    if (Capsule::table('tblproducts')->where(['configoption1' => $product['id'], 'servertype' => 'time4vps'])->exists()) {
        continue;
    }

    $product_id = Capsule::table('tblproducts')->insertGetId([
        'name' => $product['name'],
        'gid' => $gid,
        'type' => 'server',
        'description' => $product['description'],
        'autosetup' => 'payment',
        'paytype' => 'recurring',
        'servertype' => 'time4vps',
        'tax' => 1,
        'configoption1' => $product['id'],
        'configoptionsupgrade' => 1
    ]);

    /** Add product prices */
    Capsule::table('tblpricing')->insert([
        'type' => 'product',
        'relid' => $product_id,
        'currency' => 1,
        'msetupfee' => $product['prices']['m_setup'] ?? 0,
        'qsetupfee' => $product['prices']['q_setup'] ?? 0,
        'ssetupfee' => $product['prices']['s_setup'] ?? 0,
        'asetupfee' => $product['prices']['a_setup'] ?? 0,
        'bsetupfee' => $product['prices']['b_setup'] ?? 0,
        'monthly' => $product['prices']['m'] ?? -1,
        'quarterly' => $product['prices']['q'] ?? -1,
        'semiannually' => $product['prices']['s'] ?? -1,
        'annually' => $product['prices']['a'] ?? -1,
        'biennially' => $product['prices']['b'] ?? -1,
        'triennially' => -1
    ]);

    /** Create components for product */
    if ($product['components']) {
        $component_map = [
            'addons' => [],
            'components' => []
        ];

        /** Create configuration group */
        $config_gid = Capsule::table('tblproductconfiggroups')->insertGetId([
            'name' => "Configurable options for '{$product['name']}'"
        ]);

        /** Assign configuration group to product */
        Capsule::table('tblproductconfiglinks')->insertGetId(['gid' => $config_gid, 'pid' => $product_id]);

        /** Create configuration option */
        foreach ($product['components'] as $component) {
            $config_id = Capsule::table('tblproductconfigoptions')->insertGetId([
                'gid' => $config_gid,
                'optionname' => $component['name'],
                'optiontype' => 3
            ]);

            /** Add pricing */
            $option_id = Capsule::table('tblproductconfigoptionssub')->insertGetId([
                'configid' => $config_id,
                'optionname' => ''
            ]);

            Capsule::table('tblpricing')->insert([
                'type' => 'configoptions',
                'relid' => $option_id,
                'currency' => 1,
                'msetupfee' => $component['prices']['m_setup'] ?? 0,
                'qsetupfee' => $component['prices']['q_setup'] ?? 0,
                'ssetupfee' => $component['prices']['s_setup'] ?? 0,
                'asetupfee' => $component['prices']['a_setup'] ?? 0,
                'bsetupfee' => $component['prices']['b_setup'] ?? 0,
                'monthly' => $component['prices']['m'] ?? -1,
                'quarterly' => $component['prices']['q'] ?? -1,
                'semiannually' => $component['prices']['s'] ?? -1,
                'annually' => $component['prices']['a'] ?? -1,
                'biennially' => $component['prices']['b'] ?? -1,
                'triennially' => -1
            ]);

            $component_map['components'][$option_id] = $component['id'];
        }
    }

    /** Update component map */
    Capsule::table('tblproducts')
        ->where(['id' => $product_id])
        ->update([
            'configoption5' => json_encode($component_map, JSON_PRETTY_PRINT)
        ]);
}