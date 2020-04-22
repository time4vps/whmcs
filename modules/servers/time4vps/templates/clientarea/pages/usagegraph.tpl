{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>{$graph_type} usage</h3>

{foreach from=$graphs item=url key=period}
    <div class="row">
        <div class="col-12 text-center">
            <h4>{$period}</h4>
            <img src="{$url}" alt="{$period}" style="max-width: 100%;" />
        </div>
    </div>
{/foreach}