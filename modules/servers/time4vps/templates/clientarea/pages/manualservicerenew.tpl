{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{elseif $success}
    <div class="alert alert-success text-center">
        New invoice has been issued #{$newInvoiceId}.
    </div>
{/if}
<h3>Manual Service Renew</h3><br>
{if $success}
    You currently have an outstanding invoices for this service:
        <li>Invoice: #{$newInvoiceId}</li>
{elseif count($unpaidInvoice) !== 0}
    You currently have an outstanding invoices for this service:
    {foreach from=$unpaidInvoice item=$id}
        <li>Invoice: #{$id}</li>
    {/foreach}
{else}
    <form action="{$currentpagelinkback}" method="post" class="text-center">
        <div class="mt-5 mb-5">
            If you'd like to issue renewal invoice for this service sooner, click on button below.
        </div>
        <input type="hidden" name="redirect" value="{$currentpagelinkback}"/>
        <input type="hidden" name="confirm" value="1"/>
        <button class="btn btn-primary" type="submit"><i class="fa fa-wallet"></i> Renew now</button>
    </form>
{/if}