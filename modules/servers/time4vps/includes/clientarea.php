<?php

use Time4VPS\Exceptions\InvalidTaskException;

/**
 * Client Area request parser
 *
 * @param $params
 * @return array|mixed|string
 */
function time4vps_ParseClientAreaRequest($params)
{
    $action = !empty($_REQUEST['act']) ? $_REQUEST['act'] : null;

    try {
        $details = time4vps_GetServerDetails($params);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    if ($details['active_task'] && !in_array($action, ['UsageGraph', 'UsageGraphs', 'UsageHistory'])) {
        return tim4vps_ClientAreaServerBusy($details);
    }

    if ($action) {
        return call_user_func("time4vps_ClientArea{$action}", $params, $details);
    }

    return time4vps_clientAreaDefault($details);
}

/**
 * Default Client Area action
 *
 * @param array $details Server Details
 * @return array|string
 */
function time4vps_ClientAreaDefault($details)
{
    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/clientarea.tpl',
        'templateVariables' => [
            'details' => $details
        ]
    ];
}

function tim4vps_ClientAreaServerBusy($details)
{
    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/serverbusy.tpl',
        'templateVariables' => [
            'details' => $details
        ]
    ];
}

/**
 * Client Area Change DNS
 *
 * @param $params
 * @param $details
 * @return array
 */
function time4vps_ClientAreaChangeDNS($params, $details)
{
    $error = null;

    if (!empty($_POST)) {
        $error = time4vps_ChangeDNS($params, $_POST['ns1'], $_POST['ns2']);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'ChangeDNS'));
        }
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/changedns.tpl',
        'templateVariables' => [
            'details' => $details,
            'ippattern' => '((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}$',
            'error' => $error
        ]
    ];
}

/**
 * Client Area Change PTR
 *
 * @param $params
 * @param $details
 * @return array
 */
function time4vps_ClientAreaChangePTR($params, $details)
{
    $error = null;
    $ips = [];

    if (!empty($_POST['ip']) && !empty($_POST['ptr'])) {
        $error = time4vps_ChangePTR($params, $_POST['ip'], $_POST['ptr']);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'ChangePTR'));
        }
    }

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);
        $ips = array_shift($server->additionalIPs());
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/changeptr.tpl',
        'templateVariables' => [
            'details' => $details,
            'error' => $error,
            'ips' => $ips
        ]
    ];
}

/**
 * Client Area Server Reboot
 *
 * @param $params
 * @return array|string
 */
function time4vps_ClientAreaReboot($params)
{
    $last_result = null;
    $error = null;

    if (!empty($_POST['confirm'])) {
        $error =  time4vps_Reboot($params);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'Reboot'));
        }
    }

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);
        $last_result = $server->taskResult('server_reboot');
    } catch (InvalidTaskException $e) {
        // No tasks yet
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/reboot.tpl',
        'templateVariables' => [
            'last_result' => $last_result,
            'error' => $error
        ]
    ];
}

/**
 * Client Area Password Reset
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaResetPassword($params)
{
    $last_result = null;
    $error = null;

    if (!empty($_POST['confirm'])) {
        $error =  time4vps_ResetPassword($params);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'ResetPassword'));
        }
    }

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);
        $last_result = $server->taskResult('server_reset_password');
    } catch (InvalidTaskException $e) {
        // No tasks yet
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/resetpassword.tpl',
        'templateVariables' => [
            'last_result' => $last_result,
            'error' => $error
        ]
    ];
}

/**
 * Client Area Server Reinstall
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaReinstall($params)
{
    $last_result = null;
    $error = null;
    $oses = [];

    if (!empty($_POST['confirm'] && !empty($_POST['os']))) {
        $error = time4vps_ReinstallServer($params, $_POST['os']);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'Reinstall'));
        }
    }

    try {
        time4vps_InitAPI($params);

        $server = time4vps_ExtractServer($params);
        $oses = $server->availableOS();

        if ($params['configoption3']) {
            $visible_os = explode(PHP_EOL, $params['configoption3']);

            foreach ($visible_os as &$o) {
                $o = trim($o);
            }

            foreach ($oses as $idx => $os) {
                if (!in_array($os['title'], $visible_os)) {
                    unset($oses[$idx]);
                }
            }
        }

        $last_result = $server->taskResult('server_recreate');
    } catch (InvalidTaskException $e) {
        // No tasks yet
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/reinstall.tpl',
        'templateVariables' => [
            'oses' => $oses,
            'last_result' => $last_result,
            'error' => $error
        ]
    ];
}

/**
 * Client Area Change Server Hostname
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaChangeHostname($params)
{
    $last_result = null;
    $error = null;

    if (!empty($_POST['hostname'])) {
        $error = time4vps_ChangeHostname($params, $_POST['hostname']);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'ChangeHostname'));
        }
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/changehostname.tpl',
        'templateVariables' => [
            'last_result' => $last_result,
            'error' => $error
        ]
    ];
}

/**
 * Client Area Emergency Console
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaEmergencyConsole($params)
{
    $last_result = null;
    $error = null;

    if (!empty($_POST['timeout'])) {
        $error = time4vps_EmergencyConsole($params, $_POST['timeout']);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'EmergencyConsole'));
        }
    }

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);
        $last_result = $server->taskResult('server_web_console');
    } catch (InvalidTaskException $e) {
        // No tasks yet
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/emergencyconsole.tpl',
        'templateVariables' => [
            'last_result' => $last_result,
            'error' => $error
        ]
    ];
}

/**
 * Client Area Reset Firewall
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaResetFirewall($params)
{
    $last_result = null;
    $error = null;

    if (!empty($_POST['confirm'])) {
        $error = time4vps_ResetFirewall($params);
        if ($error === 'success') {
            time4vps_MarkServerDetailsObsolete($params);
            time4vps_Redirect(time4vps_ActionLink($params, 'ResetFirewall'));
        }
    }

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);
        $last_result = $server->taskResult('server_flush_iptables');
    } catch (InvalidTaskException $e) {
        // No tasks yet
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/resetfirewall.tpl',
        'templateVariables' => [
            'last_result' => $last_result,
            'error' => $error
        ]
    ];
}

/**
 * Usage graphs
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaUsageGraphs($params)
{
    $error = null;
    $graphs = [];

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);

        foreach ($server->usageGraphs() as $graph) {
            $graphs[$graph["type"]] = $graph;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/usagegraphs.tpl',
        'templateVariables' => [
            'error' => $error,
            'graphs' => $graphs,
            'url_graph_detail' => time4vps_ProductDetailsLink($params) . "&act=UsageGraph&graph="
        ]
    ];
}

/**
 * Particular usage graph
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaUsageGraph($params)
{
    $error = null;
    $graphs = [];
    $graphType = '';

    $graphTypes = ['traffic', 'io', 'load', 'iops', 'netpps', 'memory', 'cpu', 'storage'];

    if (!empty($_GET['graph']) && in_array($_GET['graph'], $graphTypes)) {
        $graphType = $_GET['graph'];
        try {
            time4vps_InitAPI($params);
            $server = time4vps_ExtractServer($params);

            foreach ($server->usageGraphs(768) as $graph) {
                preg_match("/^({$graphType})_(.*)$/", $graph["type"], $matches);

                if ($matches) {
                    $graphs[ucfirst($matches[2])] = $graph['url'];
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = "Invalid graph type.";
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/usagegraph.tpl',
        'templateVariables' => [
            'error' => $error,
            'graphs' => $graphs,
            'graph_type' => ucfirst($graphType)
        ]
    ];
}

/**
 * Usage history
 *
 * @param $params
 * @return array
 */
function time4vps_ClientAreaUsageHistory($params)
{
    $error = null;
    $usage_history = null;

    try {
        time4vps_InitAPI($params);
        $server = time4vps_ExtractServer($params);
        $usage_history = $server->usageHistory();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea/pages/usagehistory.tpl',
        'templateVariables' => [
            'error' => $error,
            'usage_history' => array_reverse($usage_history)
        ]
    ];
}
