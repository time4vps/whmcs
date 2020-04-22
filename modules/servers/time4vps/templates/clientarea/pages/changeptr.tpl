{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Change PTR</h3>

{if !$ips}
    <div class="alert alert-info">
        <i class="fa fa-fw fa-info"></i> Server does not have additional IP addresses.
    </div>
{else}
    <form class="form-horizontal" action="{$currentpagelinkback}" method="post" autocomplete="off">
        <div class="form-group">
            <label for="ip" class="col-sm-2 control-label">IP Address</label>
            <div class="col-sm-10">
                <select name="ip" class="form-control" id="ip">
                    <option></option>
                    {foreach from=$ips item=$ip}
                        <option value="{$ip}">{$ip}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="ptr" class="col-sm-2 control-label">PTR Record</label>
            <div class="col-sm-10">
                <input type="text" name="ptr" class="form-control" id="ptr" placeholder="foo.bar.tld" />
            </div>
        </div>
        <div class="block text-center">
            <button class="btn btn-primary" type="submit">Set PTR Records</button>
        </div>
    </form>
{/if}