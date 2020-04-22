{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Reset Password</h3>

{if $last_result.completed}
    <p>Your password was reset at <strong>{$last_result.completed|date_format:'%c'}</strong>:</p>
    <pre>{$last_result.results}</pre>
    <hr />
{/if}

<form action="{$currentpagelinkback}" method="post" class="text-center">
    <input type="hidden" name="confirm" value="1" />
    <button class="btn btn-primary" type="submit"><i class="fa fa-key"></i> Reset Password</button>
</form>
