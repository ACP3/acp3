{if isset($pictures)}
<div class="pictures">
{if $colorbox == 1}
	<script type="text/javascript" src="{$DESIGN_PATH}gallery/script.js"></script>
{foreach $pictures as $row}
	<a href="{$row.uri}"{if !empty($row.description)} title="{$row.description}"{/if}><img src="{uri args="gallery/image/id_`$row.id`/action_thumb"}" alt="" width="{$row.width}" height="{$row.height}"></a>
{/foreach}
{else}
{foreach $pictures as $row}
	<a href="{$row.uri}"><img src="{uri args="gallery/image/id_`$row.id`/action_thumb"}" alt="" width="{$row.width}" height="{$row.height}"></a>
{/foreach}
{/if}
</div>
{else}
<div class="error">
	<h5>{lang t="gallery|no_pictures"}</h5>
</div>
{/if}