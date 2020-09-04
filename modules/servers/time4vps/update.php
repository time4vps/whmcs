<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

require 'defines.php';
require ROOTPATH . '/init.php';

use WHMCS\Database\Capsule;

session_start();

if (!$_SESSION['adminid']) {
    die('Access denied');
}

/** Disable "Configure Server" on order page */
$tpl_path = ROOTPATH . "/templates/orderforms/standard_cart/configureproduct.tpl";
$tpl_path_copy = $tpl_path . '.orig.tpl';
$tpl_path_copy_v1 = $tpl_path . '.orig.v1.tpl';

if (!file_exists($tpl_path_copy_v1)) {
    /** Save current configureproduct.tpl */
    file_put_contents($tpl_path_copy_v1, file_get_contents($tpl_path));

    if (file_exists($tpl_path_copy)) {
        /** Restore original template in case users already has installed this module and we need to re-patch it **/
        file_put_contents($tpl_path, file_get_contents($tpl_path_copy));
    } else {
        /** Make a copy */
        file_put_contents($tpl_path_copy, file_get_contents($tpl_path));
    }

    /** Change template */
    $tpl = file_get_contents($tpl_path);
    $repl = '$1 && $productinfo.module eq "time4vps"}' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="hostname" value="{$smarty.now}.serverhost.name" />' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="rootpw" value="rootpwd" />' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="ns1prefix" value="ns1" />' . PHP_EOL;
    $repl .= "\t\t\t\t" . '<input type="hidden" name="ns2prefix" value="ns2" />' . PHP_EOL;
    $repl .= "\t\t\t" . '{else $1 && $productinfo.module neq "time4vps"}' . PHP_EOL;
    $tpl = preg_replace('/(if \$productinfo\.type eq "server")}/', $repl, $tpl);
    file_put_contents($tpl_path, $tpl);
}

/** Update products */
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

/**
 *
 * Import Time4VPS products
 *
 */

/** Map t4v product id => whmcs product id */
$product_map = [];

/** Iterate each product */
foreach ($products as $product) {

    /** Create or Update WHMCS Product */
    Capsule::table('tblproducts')->updateOrInsert([
        'configoption1' => $product['id'],
        'servertype' => 'time4vps'
    ], [
        'name' => $product['name'],
        'gid' => $gid,
        'type' => 'server',
        'description' => $product['description'],
        'autosetup' => 'payment',
        'paytype' => 'recurring',
        'tax' => 1,
        'configoptionsupgrade' => 1
    ]);

    /** Get product */
    $p = Capsule::table('tblproducts')->where([
        'configoption1' => $product['id'],
        'servertype' => 'time4vps'
    ])->first();

    if (!$p) {
        throw new Exception("Product {$product['id']} was not found");
    }

    /** Map it for later use */
    $product_map[$product['id']] = $p->id;

    /** Add product prices */
    Capsule::table('tblpricing')->updateOrInsert([
        'relid' => $p->id,
        'type' => 'product'
    ], [
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
        $cgroup = [
            'name' => "Configurable options for '{$product['name']}'"
        ];

        Capsule::table('tblproductconfiggroups')->updateOrInsert($cgroup, $cgroup);
        $cg = Capsule::table('tblproductconfiggroups')->where($cgroup)->first();

        /** Assign configuration group to product */
        $clinks = [
            'gid' => $cg->id,
            'pid' => $p->id
        ];
        Capsule::table('tblproductconfiglinks')->updateOrInsert($clinks, $clinks);

        /** Create configuration option */
        foreach ($product['components'] as $component) {
            $copt = [
                'gid' => $cg->id,
                'optionname' => $component['name'],
                'optiontype' => 3
            ];
            Capsule::table('tblproductconfigoptions')->updateOrInsert($copt, $copt);
            $co = Capsule::table('tblproductconfigoptions')->where($copt)->first();

            /** Add pricing */
            $cgroupsub = [
                'configid' => $co->id,
                'optionname' => ''
            ];

            Capsule::table('tblproductconfigoptionssub')->updateOrInsert($cgroupsub, $cgroupsub);
            $cos = Capsule::table('tblproductconfigoptionssub')->where('configid', $co->id)->first();

            Capsule::table('tblpricing')->updateOrInsert([
                'type' => 'configoptions',
                'relid' => $cos->id
            ], [
                'type' => 'configoptions',
                'relid' => $cos->id,
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

            $component_map['components'][$cos->id] = [
                'item_id' => $component['id'],
                'category_id' => $component['category_id']
            ];
        }

        /** Update component map */
        Capsule::table('tblproducts')
            ->where(['id' => $p->id])
            ->update([
                'configoption5' => json_encode($component_map, JSON_PRETTY_PRINT)
            ]);
    }
}

/** Set product upgrades */
foreach ($products as $product) {

    /** Check if product has upgrades and has product map */
    if (!$product['upgrades'] || empty($product_map[$product['id']])) {
        continue;
    }

    /** Get WHMCS product id */
    $whmcs_pid = $product_map[$product['id']];

    /** Delete upgrades */
    Capsule::table('tblproduct_upgrade_products')->where('product_id', $whmcs_pid)->delete();

    /** Create upgrades */
    foreach ($product['upgrades'] as $upgrade) {
        if (!empty($product_map[$upgrade])) {
            $upgrade_product_id = $product_map[$upgrade];
            Capsule::table('tblproduct_upgrade_products')->insertGetId([
                'product_id' => $whmcs_pid,
                'upgrade_product_id' => $upgrade_product_id
            ]);
        }
    }
}

echo 'Update complete!';