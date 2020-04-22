{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Emergency Console</h3>

<div class="alert alert-warning text-center">
    <strong><i class="fa fa-fw fa-exclamation-triangle"></i> WARNING</strong>: We do not recommend to use console for daily server access, it's designed only for emergency situations!
</div>

{if $last_result.completed}
    <p>Your last emergency console was launched at <strong>{$last_result.completed|date_format:'%c'}</strong>:</p>
    <pre>{$last_result.results}</pre>
    <hr />
{/if}

<div class="text-center margin-bottom">
    <form action="{$currentpagelinkback}" method="post">
        <div class="form-group">
            <div class="col-12">
                <p>How long do you want to access console on <strong>{$account.domain}</strong> server?</p>
                <div style="margin-bottom: 15px;">
                    {foreach from=['1h', '3h', '6h', '12h', '24h'] item=t}
                        <label style="display: inline-block; margin-right: 15px;">
                            <input type="radio" name="timeout" value="{$t}" /> {$t}
                        </label>
                    {/foreach}
                </div>
            </div>
            <div class="block text-center">
                <button class="btn btn-primary" type="submit"><i class="fa fa-terminal"></i> Launch Emergency Console</button>
            </div>
        </div>
    </form>
</div>