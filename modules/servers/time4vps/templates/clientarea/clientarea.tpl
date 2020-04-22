{if !$details}
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                Server is being provisioned. Please wait.
            </div>
        </div>
    </div>
{else}
    <h2>Server Info</h2>


    <table class="table">
        <tr>
            <td><strong>Hostname</strong></td>
            <td>{$details.domain}</td>
        </tr>
        {if $details.label}
            <tr>
                <td><strong>Label</strong></td>
                <td><span class="label label-primary">{$details.label}</span></td>
            </tr>
        {/if}
        <tr>
            <td><strong>Processor</strong></td>
            <td>{$details.cpu_cores} x {$details.cpu_frequency} Mhz</td>
        </tr>
        <tr>
            <td><strong>RAM</strong></td>
            <td>
                {$details.ram_limit} MB
                <small class="text-muted">({$details.ram_used} MB used)</small>
            </td>
        </tr>
        <tr>
            <td><strong>HDD</strong></td>
            <td>
                {$details.disk_limit / 1024} GB
                <small class="text-muted">({($details.disk_usage / 1024)|number_format:2} GB used)</small>
            </td>
        </tr>
        <tr>
            <td><strong>Bandwidth</strong></td>
            <td>
                {$details.bw_limit / 1024} GB
                <small class="text-muted">(In <span class="text-success">{$details.bw_in} MB</span>, Out <span class="text-primary">{$details.bw_out} MB</span>, Total {$details.bw_in + $details.bw_out} MB)</small>
            </td>
        </tr>
        {if $details.os}
            <tr>
                <td><strong>Main IP</strong></td>
                <td><code>{$details.ip}</code></td>
            </tr>
            {if $details.additional_ip}
                <tr>
                    <td><strong>Additional IPs</strong></td>
                    <td>
                        {foreach from=$details.additional_ip item=ip}
                            <code>{$ip}</code>
                        {/foreach}
                    </td>
                </tr>
            {/if}
        {/if}
    </table>
{/if}