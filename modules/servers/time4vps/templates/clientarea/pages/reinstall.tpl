{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Reinstall Server</h3>

{if $last_result.completed}
    <p>Your server was reinstalled at <strong>{$last_result.completed|date_format:'%c'}</strong>:</p>
    <pre>{$last_result.results}</pre>
    <hr />
{/if}

<form class="form-horizontal" action="{$currentpagelinkback}" method="post" autocomplete="off">
    <div class="form-group">
        <label for="os" class="col-sm-2 control-label">Select OS</label>
        <div class="col-sm-10">
            <select id="os" name="os" class="form-control">
                {foreach from=$oses item=os}
                    <option value="{$os.os}">{$os.title}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-12 text-center">
            <label for="confirm" class="control-label">
                <input type="checkbox" name="confirm" id="confirm" />
                I understand, that reinstalling OS will <strong class="text-danger">ERASE ALL DATA STORED ON SERVER</strong>.
            </label>
        </div>
    </div>
    <div class="block text-center">
        <button class="btn btn-danger" type="submit">Reinstall Server</button>
    </div>
</form>