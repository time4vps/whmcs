<?php
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use Time4VPS\API\Order;
use Time4VPS\API\Service;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use WHMCS\Database\Capsule;

/**
 * Create account
 *
 * @param $params
 * @return string|void
 * @throws Exception
 */
function time4vps_CreateAccount($params)
{
    time4vps_InitAPI($params);

    try {
        if ($server = time4vps_ExtractServer($params)) {
            throw new Exception('Service is already created');
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }

    $product_id = $params['configoption1'];

    try {
        $order = new Order();
        $order = $order->create($product_id, 'serverhost.name', time4vps_BillingCycle($params['model']['billingcycle']), time4vps_ExtractComponents($params));

        $service_id = (new Service())->fromOrder($order['order_num']);

        Capsule::table(TIME4VPS_TABLE)->insert([
            'external_id' => $service_id,
            'service_id' => $params['serviceid'],
            'details_updated' => null
        ]);
    } catch (Exception $e) {
        return 'Cannot create account. ' . $e->getMessage();
    }
    return 'success';
}

/**
 * Terminate account
 *
 * @param $params
 * @return string
 */
function time4vps_TerminateAccount($params)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    try {
        (new Service($server->id()))->cancel('No need, terminated by API', true);
    } catch (Exception $e) {
        return 'Cannot terminate account. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Upgrade package or config option
 *
 * @param $params
 * @return string
 * @throws Exception
 */
function time4vps_ChangePackage($params)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    try {
        $service = new Service($server->id());
    } catch (Exception $e) {
        return $e->getMessage();
    }

    $details = $server->details();

    if ((int) $params['configoption1'] !== (int) $details['package_id']) {
        $service->orderUpgrade(['package' => $params['configoption1']], time4vps_BillingCycle($params['model']['billingcycle']));
    }

    $service->orderUpgrade(['resources' => time4vps_ExtractComponents($params, false)], time4vps_BillingCycle($params['model']['billingcycle']));

    return 'success';
}

/**
 * Changes server password
 *
 * @param $params
 * @return string
 */
function time4vps_ResetPassword($params)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->resetPassword();
    } catch (Exception $e) {
        return 'Cannot change server password. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Reboot server
 *
 * @param $params
 * @return string
 */
function time4vps_Reboot($params)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->reboot();
    } catch (Exception $e) {
        return 'Cannot reboot server. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Change DNS Servers
 *
 * @param $params
 * @param string $ns1
 * @param string $ns2
 * @param string $ns3
 * @param string $ns4
 * @return string
 */
function time4vps_ChangeDNS($params, $ns1, $ns2 = '', $ns3 = '', $ns4 = '')
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_extractServer($params);
        $server->setDNS($ns1, $ns2, $ns3, $ns4);
    } catch (Exception $e) {
        return 'Cannot change DNS. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Change PTR Record
 *
 * @param $params
 * @param $ip
 * @param $ptr
 * @return string
 */
function time4vps_ChangePTR($params, $ip, $ptr)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->setPTR($ip, $ptr);
    } catch (Exception $e) {
        return 'Cannot change PTR. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Reinstall server
 *
 * @param $params
 * @param $os
 * @return string
 */
function time4vps_ReinstallServer($params, $os)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->reinstall($os, null, $params['configoption2']);
    } catch (Exception $e) {
        return 'Cannot reinstall server. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Change server hostname
 *
 * @param $params
 * @param $hostname
 * @return string
 */
function time4vps_ChangeHostname($params, $hostname)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->rename($hostname);
    } catch (Exception $e) {
        return 'Cannot rename server. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Launch emergency console
 *
 * @param $params
 * @param $timeout
 * @return string
 */
function time4vps_EmergencyConsole($params, $timeout)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->emergencyConsole($timeout);
    } catch (Exception $e) {
        return 'Cannot launch emergency console. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Reset firewall to default settings
 *
 * @param $params
 * @return string
 */
function time4vps_ResetFirewall($params)
{
    time4vps_InitAPI($params);

    try {
        $server = time4vps_ExtractServer($params);
        $server->flushFirewall();
    } catch (Exception $e) {
        return 'Cannot reset firewall. ' . $e->getMessage();
    }

    return 'success';
}

/**
 * Update server details table
 *
 * @param $params
 * @param bool $force
 * @return array|false
 * @throws APIException
 * @throws AuthException
 * @throws \Time4VPS\Exceptions\Exception
 */
function time4vps_GetServerDetails($params, $force = false)
{
    $row = Capsule::table(TIME4VPS_TABLE)->where('service_id', $params['serviceid'])->first();

    $current_details = $row->details ? json_decode($row->details, true) : [];
    $last_update = $row->details_updated ? strtotime($row->details_updated) : null;

    if ($force || !$current_details || !$last_update || $current_details['active_task'] || time() - $last_update > 5 * 60) {

        $update = [];

        time4vps_InitAPI($params);

        $server = time4vps_ExtractServer($params);

        if (!$server) {
            throw new Exception('Server does not exist');
        }

        // Update service details
        $service = (new Service($server->id()))->details();

        $update['lastupdate'] = date('Y-m-d H:i:s', time());
        $update['domainstatus'] = $service['status'];

        if ($update['domainstatus'] === 'Active') {

            // Update server details
            $details = $server->details();

            if ($details['active_task']) {
                return $details;
            }

            $dns_servers = !empty($details['dns_servers']) ? $details['dns_servers'] : [];

            // Set password to empty
            if (!$details['os']) {
                $update['username'] = '';
                $update['password'] = '';
            }

            // Usage
            $update = array_merge([
                'domain' => $details['domain'],
                'diskusage' => $details['disk_usage'],
                'disklimit' => $details['disk_limit'],
                'bwusage' => $details['bw_in'] + $details['bw_out'],
                'bwlimit' => $details['bw_limit'],
                'dedicatedip' => $details['ip'],
                'assignedips' => implode(',', $details['additional_ip']),
                'ns1' => !empty($dns_servers[0]) ? $dns_servers[0] : null,
                'ns2' => !empty($dns_servers[1]) ? $dns_servers[1] : null,
                'username' => preg_match('/^kvm-win-/', $details['os']) ? 'Administrator' : 'root',
            ], $update);

            // Extract password
            foreach ($details['last_tasks'] as $task) {
                if (in_array($task['name'], ['server_reset_password', 'server_recreate']) && $task['results']) {
                    preg_match('/Password:\s(.*)\s?/', $task['results'], $new_password);
                    if ($new_password) {
                        /** @noinspection PhpUndefinedFunctionInspection */
                        $update['password'] = encrypt(trim($new_password[1]));
                    }
                }
            }

            // Extract components
            $map = json_decode($params['configoption5'], true);
            $component_map = array_flip($map['components']);

            foreach ($details['components'] as $component) {
                if (empty($component_map[$component['id']])) {
                    continue;
                }

                Capsule::table('tblhostingconfigoptions')
                    ->where([
                        'relid' => $params['serviceid'],
                        'configid' => $component_map[$component['id']],
                        'optionid' => $component_map[$component['id']]
                    ])
                    ->update(['qty' => $component['selected'] ? 1 : 0]);
            }

            // Unset last tasks
            unset($details['last_tasks']);

            // Cache data
            Capsule::table(TIME4VPS_TABLE)
                ->where('service_id', $params['serviceid'])
                ->update([
                    'details' => json_encode($details),
                    'details_updated' => $update['lastupdate']
                ]);

            $current_details = $details;
        }

        // Update WHMCS info
        Capsule::table('tblhosting')
            ->where('id', $params['serviceid'])
            ->update($update);
    }

    return $current_details;
}

/**
 * Update server details table and mark details as obsolete
 *
 * @param $params
 */
function time4vps_MarkServerDetailsObsolete($params)
{
    /** @noinspection PhpUndefinedClassInspection */
    Capsule::table(TIME4VPS_TABLE)
        ->where('service_id', $params['serviceid'])
        ->update([
            'details_updated' => null
        ]);
}