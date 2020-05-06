<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

require 'defines.php';
require ROOTPATH . '/init.php';

use WHMCS\Database\Capsule;

if (!$_SESSION['adminid']) {
    die('Access denied');
}

/** Backwards compatability */
if (Capsule::schema()->hasTable('sm_time4vps')) {
    try {
        Capsule::schema()
            ->table('sm_time4vps', function ($table) {
                /** @var $table Object */
                $table->text('details')->nullable();
                $table->timestamp('details_updated')->nullable();
            });
    } catch (PDOException $e) {
        // Column already exists
        if ($e->getCode() !== '42S21') {
            throw $e;
        }
    }
    Capsule::schema()->rename('sm_time4vps', TIME4VPS_TABLE);
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

if ($_GET['truncate']) {

    // Delete components
    Capsule::table('tblproductconfiglinks')->truncate();
    Capsule::table('tblproductconfiggroups')->truncate();
    Capsule::table('tblproductconfigoptions')->truncate();
    Capsule::table('tblproductconfigoptionssub')->truncate();

    // Delete products
    Capsule::table('tblproducts')->truncate();

    // Delete upgrades
    Capsule::table('tblproduct_upgrade_products')->truncate();

    // Delete pricing
    Capsule::table('tblpricing')->truncate();

    // Upgrades
    Capsule::table('tblupgrades')->truncate();

    // Config options
    Capsule::table('tblhostingconfigoptions')->truncate();

    echo 'Product tables truncated. ';
} else {
    echo 'Install successfull. ';
}
echo 'If you want to import all products from Time4VPS, run <a href="update.php">update</a>.';