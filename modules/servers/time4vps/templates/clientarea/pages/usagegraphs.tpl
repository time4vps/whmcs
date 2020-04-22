{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>Usage Graphs</h3>

{if $graphs}
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h4>Traffic</h4>
            <a href="{$url_graph_detail}traffic"><img src="{$graphs.traffic_daily.url}" alt="Traffic" style="width: 100%;" /></a>
        </div>
        <div class="col-md-6 col-sm-12">
            <h4>Packets</h4>
            <a href="{$url_graph_detail}netpps"><img src="{$graphs.netpps_daily.url}" alt="Packets" style="width: 100%;" /></a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h4>CPU</h4>
            <a href="{$url_graph_detail}cpu"><img src="{$graphs.cpu_daily.url}" alt="CPU" style="width: 100%;" /></a>
        </div>
        <div class="col-md-6 col-sm-12">
            <h4>Load</h4>
            <a href="{$url_graph_detail}load"><img src="{$graphs.load_daily.url}" alt="Load" style="width: 100%;" /></a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h4>I/O</h4>
            <a href="{$url_graph_detail}io"><img src="{$graphs.io_daily.url}" alt="I/O" style="width: 100%;" /></a>
        </div>
        <div class="col-md-6 col-sm-12">
            <h4>IOPS</h4>
            <a href="{$url_graph_detail}iops"><img src="{$graphs.iops_daily.url}" alt="IOPS" style="width: 100%;" /></a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h4>Memory</h4>
            <a href="{$url_graph_detail}memory"><img src="{$graphs.memory_daily.url}" alt="Memory" style="width: 100%;" /></a>
        </div>
        <div class="col-md-6 col-sm-12">
            <h4>Storage</h4>
            <a href="{$url_graph_detail}storage"><img src="{$graphs.storage_daily.url}" alt="Storage" style="width: 100%;" /></a>
        </div>
    </div>
{else}
    <div class="alert alert-info">
        Server graphs are not available yet.
    </div>
{/if}