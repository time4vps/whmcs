<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

require '../defines.php';
require ROOTPATH . '/init.php';

use WHMCS\Database\Capsule;

session_start();

if (!$_SESSION['adminid']) {
    die('Access denied');
}

require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../includes/helpers.php';

$data = Capsule::table('tblservers')->where('type', 'time4vps')->first();

if (!$data) {
    die('No module server found');
}

// Save function
if ($_POST) {
    foreach ($_POST['products'] as $product_id => $components) {
        $component_map = [
            'addons' => [],
            'components' => []
        ];
        foreach ($components as $component_id => $map) {
            $map = explode('_', $map);
            $component_map['components'][$component_id] = [
                'item_id' => (int) $map[0],
                'category_id' => (int) $map[1]
            ];
        }

        Capsule::table('tblproducts')
            ->where(['id' => $product_id])
            ->update([
                'configoption5' => json_encode($component_map, JSON_PRETTY_PRINT)
            ]);
    }
}


$decrypt = localAPI('DecryptPassword', ['password2' => $data->password]);

$api = [
    'serverhttpprefix' => $data->secure === 'on' ? 'https' : 'http',
    'serverhostname' => $data->hostname,
    'serverusername' => $data->username,
    'serverpassword' => $decrypt['password']
];

time4vps_InitAPI($api);

$t4v_products = [];
$available_vps = (new Time4VPS\API\Product())->getAvailableVPS();
foreach ($available_vps as $vps) {
    $t4v_products[$vps['id']] = $vps;
}

$products = Capsule::table('tblproducts')->where([
    'servertype' => 'time4vps'
])->get();

echo '<!doctype html><html><head><title>Component mapping</title></head><body>';
echo '<form method="post">';
foreach ($products as $product) {

    if (empty($t4v_products[$product->configoption1])) {
        continue;
    }

    $t4v_product = $t4v_products[$product->configoption1];

    $local_components = Capsule::table('tblproductconfiglinks')
        ->select('tblproductconfigoptions.*')
        ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
        ->where('pid', $product->id)
        ->get();

    echo "<h1>{$product->name}</h1>";

    echo "<ul>";

    $current_map = json_decode($product->configoption5, true);

    foreach ($local_components as $local_component) {
        $selected_id = 0;
        if (!empty($current_map['components'][$local_component->id])) {
            $selected_id = $current_map['components'][$local_component->id]['item_id'];
        }

        echo "<li><label style='min-width: 400px; display: inline-block;'>{$local_component->optionname} (ID: {$local_component->id})</label>";
        echo "<select name='products[{$product->id}][{$local_component->id}]'>";
        echo '<option></option>';
        foreach ($t4v_product['components'] as $t4v_component) {
            echo "<option value='{$t4v_component['id']}_{$t4v_component['category_id']}'" . ($selected_id === $t4v_component['id'] ? ' selected' : '') . ">{$t4v_component['name']} (ID: {$t4v_component['id']})</option>";
        }
        echo '</select>';

        echo "</li>";
    }

    echo "</ul>";

    echo '<hr />';
}
echo '<input type="submit" />';
echo '</form>';
echo '</body></html>';