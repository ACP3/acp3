<div id="breadcrumb">
    {block BREADCRUMB}
        {include file="asset:System/Partials/breadcrumb.tpl" breadcrumb=$BREADCRUMB}
    {/block}
</div>
<h2>{page_title}</h2>
{event name="layout.content_before"}
{block CONTENT}{/block}
<!-- JAVASCRIPTS -->
