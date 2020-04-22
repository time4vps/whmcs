{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Change Hostname</h3>

<div class="alert alert-info text-center">
    <i class="fa fa-fw fa-info-circle"></i> Before changing hostname, make sure that hostname A record is pointed to your server IP address.
</div>

<form class="form-horizontal" action="{$currentpagelinkback}" method="post" autocomplete="off">
    <div class="form-group">
        <label for="hostname" class="col-sm-2 control-label">Hostname</label>
        <div class="col-sm-10">
            <input type="text" id="hostname" name="hostname" class="form-control" placeholder="foo.bar.tld" />
        </div>
    </div>
    <div class="block text-center">
        <button class="btn btn-primary" type="submit"><i class="fa fa-edit"></i> Rename</button>
    </div>
</form>