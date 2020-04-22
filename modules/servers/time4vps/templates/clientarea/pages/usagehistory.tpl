{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Usage History</h3>

{if $usage_history}
    <table class="checker table table-striped" style="width: 100%">
        <thead>
        <tr>
            <th>Period</th>
            <th class="text-center">Download</th>
            <th class="text-center">Upload</th>
            <th class="text-center">Total Bandwidth</th>
            <th class="text-center">Disk Quota</th>
            <th class="text-center">Inode Quota</th>
        </tr>
        </thead>
        {foreach from=$usage_history item=item}
            <tr>
                {assign var=year value=$item.year}
                {assign var=month value=$item.month}
                {assign var=history_date value="$year-$month-01"}
                <td><strong>{$history_date|date_format:"%B, %Y"}</strong></td>
                <td class="text-center">{($item.bw_in/1024)|number_format:3} GB</td>
                <td class="text-center">{($item.bw_out/1024)|number_format:3} GB</td>
                <td class="text-center">{(($item.bw_in+$item.bw_out)/1024)|number_format:3} GB</td>
                <td class="text-center">
                    {if $item.quota_usage}
                        {($item.quota_usage/1024)|number_format:2} GB / {($item.quota_limit/1024)|number_format:0} GB
                    {else}
                        -
                    {/if}
                </td>
                <td class="text-center">
                    {if $item.inode_usage}
                        {$item.inode_usage} / {$item.inode_limit}
                    {else}
                        -
                    {/if}
                </td>
            </tr>
        {/foreach}
    </table>
{else}
    <div class="alert alert-info">
        Server usage history is not available yet.
    </div>
{/if}