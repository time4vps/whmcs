<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            {assign var=active_task value=$details.active_task}
            {assign var=time value=$smarty.now|date_format:"%s"}
            {assign var=seconds value=$active_task.activated|date_format:"%s"}
            {assign var=seconds value=$time-$seconds}
            {assign var=minutes value=$seconds/60|string_format:"%d"}
            {assign var=seconds value=$seconds%60}

            <p class="text-center"><i class="fa fa-fw fa-2x fa-spin fa-sync-alt"></i></p>
            <p class="text-center"><span class="fa fa-fw fa-cog"></span> Server <strong>is busy</strong>.</p>
            <p class="text-center"><span class="fa fa-fw fa-sign-in"></span> Task initiated: <strong>{$active_task.activated|date_format:'%c'}</strong></p>
            {if $minutes|intval >= 0 && $seconds|intval > 0}
                <p class="text-center"><span class="fa fa-fw fa-tag"></span> Time Elapsed: <strong>{$minutes|intval}m {$seconds|intval}s</strong></p>
            {/if}
        </div>
    </div>
</div>

<script>
    {literal}
    setInterval(function () {
        window.location.reload(true);
    }, 30000);
    {/literal}
</script>