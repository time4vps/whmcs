{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Change DNS servers</h3>

<form class="form-horizontal" action="{$currentpagelinkback}" method="post" autocomplete="off">
    <div class="form-group">
        <label for="dns1" class="col-sm-2 control-label">DNS Server 1</label>
        <div class="col-sm-10">
            <input type="text" name="ns1" class="form-control" id="dns1" placeholder="IPv4 Address" required pattern="{$ippattern}" value="{$details.dns_servers[0]}" />
        </div>
    </div>
    <div class="form-group">
        <label for="dns2" class="col-sm-2 control-label">DNS Server 2</label>
        <div class="col-sm-10">
            <input type="text" name="ns2" class="form-control" id="dns2" placeholder="IPv4 Address" pattern="{$ippattern}" value="{$details.dns_servers[1]}" />
        </div>
    </div>
    <div class="block text-center">
        <button class="btn btn-primary" type="submit">Set DNS Servers</button>
    </div>
</form>
