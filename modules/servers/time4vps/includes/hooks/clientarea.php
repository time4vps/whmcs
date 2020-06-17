<?php /** @noinspection ALL */

use WHMCS\Database\Capsule;

add_hook('ClientAreaPrimarySidebar', 1, function ($sidebar) {

    if ($_REQUEST['action'] !== 'productdetails' || !$_REQUEST['id']) {
        return;
    }

    $id = $_REQUEST['id'];

    if (!Capsule::table('tblhosting')
        ->join('tblservers', 'tblservers.id', '=', 'tblhosting.server')
        ->where('tblhosting.id', $id)
        ->where('tblhosting.domainstatus', 'Active')
        ->where('tblservers.name', 'Time4VPS')
        ->first()) {
        return;
    }


    $actions = $sidebar->addChild(
        'actionsMenu', [
            'name' => 'Manage Server',
            'label' => Lang::trans('Management'),
            'order' => 15,
            'icon' => 'fas fa-cogs',
        ]
    );

    $actions->addChild(
        'actionsMenuRebootItem', [
            'name' => 'Reboot',
            'label' => Lang::trans('Reboot'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=Reboot",
            'order' => 0,
            'icon' => 'fa-fw fa-sync-alt'
        ]
    );

    $actions->addChild(
        'actionsMenuResetPasswordItem', [
            'name' => 'Reset Password',
            'label' => Lang::trans('Reset Password'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=ResetPassword",
            'order' => 1,
            'icon' => 'fa-key'
        ]
    );

    $actions->addChild(
        'actionsMenuChangeHostnameItem', [
            'name' => 'Change Hostname',
            'label' => Lang::trans('Change Hostname'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=ChangeHostname",
            'order' => 2,
            'icon' => 'fa-fw fa-edit'
        ]
    );

    $actions->addChild(
        'actionsMenuReinstallItem', [
            'name' => 'Reinstall',
            'label' => Lang::trans('Reinstall'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=Reinstall",
            'order' => 3,
            'icon' => 'fa-fw fa-wrench'
        ]
    );

    $actions->addChild(
        'actionsMenuEmergencyConsoleItem', [
            'name' => 'Emergency Console',
            'label' => Lang::trans('Emergency Console'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=EmergencyConsole",
            'order' => 4,
            'icon' => 'fa-fw fa-terminal'
        ]
    );

    $actions->addChild(
        'actionsMenuChangeDNSItem', [
            'name' => 'Change DNS',
            'label' => Lang::trans('Change DNS'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=ChangeDNS",
            'order' => 5,
            'icon' => 'fa-fw fa-tags'
        ]
    );

    $actions->addChild(
        'actionsMenuResetFirewallItem', [
            'name' => 'Reset Firewall',
            'label' => Lang::trans('Reset Firewall'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=ResetFirewall",
            'order' => 6,
            'icon' => 'fa-fw fa-fire'
        ]
    );

    $actions->addChild(
        'actionsMenuChangePTRItem', [
            'name' => 'Change PTR',
            'label' => Lang::trans('Change PTR'),
            'uri' => "clientarea.php?action=productdetails&id={$id}&act=ChangePTR",
            'order' => 7,
            'icon' => 'fa-fw fa-search'
        ]
    );
});

add_hook('ClientAreaSecondarySidebar', 1, function ($sidebar)
{
    if ($_REQUEST['action'] !== 'productdetails' || !$_REQUEST['id']) {
        return;
    }

    $id = $_REQUEST['id'];

        if (!Capsule::table('tblhosting')
            ->join('tblservers', 'tblservers.id', '=', 'tblhosting.server')
            ->where('tblhosting.id', $id)
            ->where('tblhosting.domainstatus', 'Active')
            ->where('tblservers.name', 'Time4VPS')
            ->first()) {
            return;
        }

        $info = $sidebar->addChild(
            'actionsMenu', [
                'name' => 'Server Information',
                'label' => Lang::trans('Server Information'),
                'order' => 15,
                'icon' => 'fas fa-info',
            ]
        );

        $info->addChild(
            'infoMenuUsageGraphsItem', [
                'name' => 'Usage Graphs',
                'label' => Lang::trans('Usage Graphs'),
                'uri' => "clientarea.php?action=productdetails&id={$id}&act=UsageGraphs",
                'order' => 0,
                'icon' => 'fa-chart-area',
            ]
        );

        $info->addChild(
            'infoMenuUsageHistoryItem', [
                'name' => 'Usage History',
                'label' => Lang::trans('Usage History'),
                'uri' => "clientarea.php?action=productdetails&id={$id}&act=UsageHistory",
                'order' => 0,
                'icon' => 'fa-chart-bar',
            ]
        );
});

add_hook('ClientAreaFooterOutput', 1, function ($params) {
    if ($params['module'] === 'time4vps' && $params['action'] === 'productdetails') {
        return '<script type="text/javascript" src="modules/servers/time4vps/assets/js/clientarea.js"></script>';
    }
});
